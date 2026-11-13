import json
import os
import re
import shlex
import subprocess
import sys


def main():
    request = json.load(sys.stdin)
    if not re.fullmatch(r"\.[a-zA-Z0-9]+", request["extension"]):
        sys.exit(70)

    filename = "Main" + request["extension"]
    with open(filename, "w", encoding="utf-8") as source:
        source.write(request["code"])

    def execute(command, input_text=None, compiling=False):
        command = command.replace("{file}", shlex.quote(filename)).replace("{dir}", "/workspace")
        result = subprocess.run(
            command,
            shell=True,
            input=input_text.encode("utf-8") if input_text is not None else b"",
            stdout=subprocess.DEVNULL if compiling else None,
            timeout=request["timeout"],
        )
        if result.returncode:
            sys.exit(65 if compiling else 70)

    try:
        if request["compile_command"]:
            execute(request["compile_command"], compiling=True)
        execute(request["run_command"], request["input"])
    except subprocess.TimeoutExpired:
        # PID 1 exits so the runtime also kills any child processes left behind.
        os._exit(124)


if __name__ == "__main__":
    main()
