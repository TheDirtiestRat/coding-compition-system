<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Candidates;
use App\Models\Challenges;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'user_type' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
        ]);

        User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('user'),
        ]);

        // insert option values
        DB::table('options')->insert([
            'option_name' => 'start_compition',
            'option_datatype' => 'boolean',
            'option_value' => '0'
        ]);
        DB::table('options')->insert([
            'option_name' => 'set_time',
            'option_datatype' => 'text',
            'option_value' => '00:00:00'
        ]);
        DB::table('options')->insert([
            'option_name' => 'expected_end_time',
            'option_datatype' => 'text',
            'option_value' => '00:00:00'
        ]);

        Candidates::factory(5)->create();

        Challenges::factory()->create([
            'title' => 'Hello World (Python)',
            'description' => 'Write a Python program that prints "Hello, world!" to the console.',
            'points' => 10,
            'language' => 'python',
            'difficulty' => 'easy',
            'code_solution' => <<<PYTHON
print("Hello, world!")
PYTHON
        ]);

        Challenges::factory()->create([
            'title' => 'Sum of Two Numbers (Java)',
            'description' => 'Write a Java program that declares two integers a = 10 and b = 5, computes their sum, and prints: "The sum of A and B is = 15".',
            'points' => 15,
            'difficulty' => 'easy',
            'language' => 'java',
            'code_solution' => <<<JAVA
public class Main {
    public static void main(String[] args) {
        int a = 10;
        int b = 5;
        int sum = a + b;

        System.out.println("The sum of A and B is = " + sum);
    }
}
JAVA
        ]);

        Challenges::factory()->create([
            'title' => 'Factorial Calculator (Python)',
            'description' => 'Write a Python program that calculates the factorial of a given number n = 5 and prints the result.',
            'points' => 25,
            'language' => 'python',
            'difficulty' => 'medium',
            'code_solution' => <<<PYTHON
n = 5
factorial = 1

for i in range(1, n + 1):
    factorial *= i

print(f"Factorial of {n} is {factorial}")
PYTHON
        ]);

        Challenges::factory()->create([
            'title' => 'Reverse a String (Java)',
            'description' => 'Write a Java program that reverses the string "hello" and prints the reversed result.',
            'points' => 25,
            'language' => 'java',
            'difficulty' => 'medium',
            'code_solution' => <<<JAVA
public class Main {
    public static void main(String[] args) {
        String input = "hello";
        String reversed = "";

        for (int i = input.length() - 1; i >= 0; i--) {
            reversed += input.charAt(i);
        }

        System.out.println(reversed);
    }
}
JAVA
        ]);

        Challenges::factory()->create([
            'title' => 'Prime Number Checker (Python)',
            'description' => 'Write a Python program that checks whether a number n = 29 is a prime number.',
            'points' => 40,
            'language' => 'python',
            'difficulty' => 'hard',
            'code_solution' => <<<PYTHON
n = 29
is_prime = True

if n <= 1:
    is_prime = False
else:
    for i in range(2, int(n ** 0.5) + 1):
        if n % i == 0:
            is_prime = False
            break

if is_prime:
    print(f"{n} is a prime number")
else:
    print(f"{n} is not a prime number")
PYTHON
        ]);

        Challenges::factory()->create([
            'title' => 'Fibonacci Sequence (Java)',
            'description' => 'Write a Java program that prints the first 10 numbers in the Fibonacci sequence.',
            'points' => 40,
            'language' => 'java',
            'difficulty' => 'hard',
            'code_solution' => <<<JAVA
public class Main {
    public static void main(String[] args) {
        int n = 10;
        int a = 0, b = 1;

        for (int i = 0; i < n; i++) {
            System.out.print(a + " ");
            int next = a + b;
            a = b;
            b = next;
        }
    }
}
JAVA
        ]);

        Challenges::factory()->create([
            'title' => 'Palindrome Checker (Python)',
            'description' => 'Write a Python program that checks whether a given string "racecar" is a palindrome. Ignore case sensitivity.',
            'points' => 75,
            'language' => 'python',
            'difficulty' => 'challenge',
            'code_solution' => <<<PYTHON
text = "racecar"
normalized = text.lower()

if normalized == normalized[::-1]:
    print(f'"{text}" is a palindrome')
else:
    print(f'"{text}" is not a palindrome')
PYTHON
        ]);
    }
}
