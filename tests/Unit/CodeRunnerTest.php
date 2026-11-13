<?php

namespace Tests\Unit;

use App\Models\ProgrammingLanguage;
use App\Services\CodeRunner;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class CodeRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container;
        $container->instance('files', new Filesystem);
        Facade::setFacadeApplication($container);
    }

    protected function tearDown(): void
    {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);

        parent::tearDown();
    }

    private function language(): ProgrammingLanguage
    {
        return new ProgrammingLanguage([
            'file_extension' => '.php',
            'run_command' => '"'.PHP_BINARY.'" {file}',
        ]);
    }

    public function test_preserves_zero_input(): void
    {
        $output = (new CodeRunner)->run('<?php echo stream_get_contents(STDIN);', $this->language(), '0');

        $this->assertSame('0', $output);
    }

    public function test_reports_runtime_failure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('failed');

        (new CodeRunner)->run('<?php fwrite(STDERR, "failed"); exit(1);', $this->language());
    }

    public function test_stops_code_that_exceeds_timeout(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Process timed out');

        (new CodeRunner)->run('<?php sleep(3);', $this->language(), '', 1);
    }

    public function test_rejects_extensions_that_escape_the_work_directory(): void
    {
        $language = $this->language();
        $language->file_extension = '/../outside.php';
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid programming language configuration.');

        (new CodeRunner)->run('<?php echo "hello";', $language);
    }
}
