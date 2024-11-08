<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MainController extends Controller
{
    public function home(): View
    {
        return view('home'); // Ajuste no método view para minúsculas
    }

    public function generateExercises(Request $request):View
    {
        $request->validate([
            'number_one' => 'required|integer|min:0|max:999|lt:number_two',
            'number_two' => 'required|integer|min:0|max:999|gt:number_one',
            'number_exercises' => 'required|integer|min:5|max:50',
            'check_sum' => 'required_without_all:check_subtraction,check_multiplication,check_division',
            'check_subtraction' => 'required_without_all:check_sum,check_multiplication,check_division',
            'check_multiplication' => 'required_without_all:check_sum,check_subtraction,check_division',
            'check_division' => 'required_without_all:check_sum,check_subtraction,check_multiplication',
        ], [
            'check_sum.required_without_all' => 'Por favor, selecione ao menos uma operação.',
        ]);

        // Definindo as operações selecionadas
        $operations = [];

        if ($request->check_sum) { $operations[] = 'sum';}
        if ($request->check_subtraction) { $operations[] = 'subtraction';}
        if ($request->check_multiplication) { $operations[] = 'multiplication';}
        if ($request->check_division) { $operations[] = 'division';}


        $min = $request->number_one;
        $max = $request->number_two;
        $numberExercises = $request->number_exercises;

        // Gerando os exercícios
        $exercises = [];
        for ($index = 1; $index <= $numberExercises; $index++) {

            $exercises[] = $this->generateExercise($index, $operations, $min, $max);

        }
        //place exercices in session

        session(['exercises' => $exercises]);

       return view ('operations', ['exercises' => $exercises]);
    }

    public function printExercises()
    {
        // Verifica se existem exercícios na sessão
        if (!session()->has('exercises')) {
            return redirect()->route('home');
        }

        $exercises = session('exercises');

        echo '<pre>';
        echo '<h1>Exercícios de Matemática (' . env('APP_NAME') . ')</h1>';
        echo '<hr>';

        foreach ($exercises as $exercise) {
            echo '<h2><small>' . str_pad($exercise['exercise_number'], 2, "0", STR_PAD_LEFT) . ' » </small>' . $exercise['exercise'] . '</h2>';
        }

       //soluttions
       echo '<hr>';
       echo '<small>Soluções<small><br>';
       foreach ($exercises as $exercise) {
        echo '<small>' . str_pad($exercise['exercise_number'], 2, "0", STR_PAD_LEFT) . ' » ' . $exercise['sollution'] . '</small><br>';
    }

    }

    public function exportExercises()
    {
        echo 'Exportar exercícios para um arquivo de texto';
    }


    private function generateExercise($index, $operations, $min, $max ):array
    {
        $operation = $operations[array_rand($operations)];
        $number1 = rand($min, $max);
        $number2 = rand($min, $max);

        $exercise = '';
        $sollution = '';


        switch ($operation) {
            case 'sum':
                $exercise = "$number1 + $number2 =";
                $sollution = $number1 + $number2;
                break;
            case 'subtraction':
                $exercise = "$number1 - $number2 =";
                $sollution = $number1 - $number2;
                break;
            case 'multiplication':
                $exercise = "$number1 x $number2 =";
                $sollution = $number1 * $number2;
                break;
            case 'division':
                $number2 = $number2 == 0 ? 1 : $number2; // Evitar divisão por zero
                $exercise = "$number1 : $number2 =";
                $sollution = $number1 / $number2;
                break;
        }

        if(is_float($sollution)){
            $sollution = round($sollution, 2 );
        }

        return  [
            'operation' =>$operation,
            'exercise_number' => $index,
            'exercise' => $exercise,
            'sollution' => "$exercise $sollution",
        ];
        
    }
}
