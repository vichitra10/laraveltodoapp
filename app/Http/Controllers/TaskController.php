<?php

namespace App\Http\Controllers;
use App\Models\Task;

use Illuminate\Http\Request;

class TaskController extends Controller
{
        public function index()
        {
            $tasks = Task::all();
            return view('index', compact('tasks'));
        }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:tasks',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'completed' => false,
        ]);

        return response()->json($task);
    }


    public function showtasks()
    {

        $tasks = Task::where('completed', false)->get();
        return response()->json($tasks);
    }


    public function showalltasks()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }


    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        if ($task) {
            $task->completed = $request->input('completed') === 'true';
            $task->save();
            return response()->json(['message' => 'Task updated successfully']);
        }
        return response()->json(['message' => 'Task not found'], 404);
    }



    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.']);
    }



}
