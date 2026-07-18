<?php

namespace Database\Seeders;

use App\Models\ProgrammingLanguage;
use Illuminate\Database\Seeder;

class ProgrammingLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $javaPath = 'C:\Program Files\Java\jdk-23\bin';

        ProgrammingLanguage::create([
            'name' => 'java',
            'compile_command' => '"' . $javaPath . '\javac.exe" {file}',
            'run_command' => '"' . $javaPath . '\java.exe" -cp {dir} Main',
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
