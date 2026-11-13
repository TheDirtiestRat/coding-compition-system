<?php

namespace Database\Seeders;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Seeder;

class ProgrammingLanguageSeeder extends Seeder
{
    public function run(): void
    {
        ProgrammingLanguage::create([
            'name' => 'java',
            'compile_command' => 'javac {file}',
            'run_command' => 'java -cp {dir} Main',
            'file_extension' => '.java',
            'is_active' => true,
        ]);

        ProgrammingLanguage::create([
            'name' => 'python',
            'compile_command' => null,
            'run_command' => 'python {file}',
            'file_extension' => '.py',
            'is_active' => true,
        ]);
    }
}
