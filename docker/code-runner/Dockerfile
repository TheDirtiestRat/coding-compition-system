FROM eclipse-temurin:25-jdk-jammy

RUN apt-get update \
    && apt-get install -y --no-install-recommends python3 python-is-python3 \
    && rm -rf /var/lib/apt/lists/*

COPY runner.py /runner.py
ENV HOME=/workspace TMPDIR=/workspace JAVA_TOOL_OPTIONS="-Xmx128m -XX:ActiveProcessorCount=1 -XX:-UsePerfData"
USER 65534:65534
WORKDIR /workspace
ENTRYPOINT ["python3", "-I", "/runner.py"]
