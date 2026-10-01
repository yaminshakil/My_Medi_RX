<?php

namespace App\Http\Controllers\Admin\Eprescriptions;

use App\Http\Controllers\Controller;
use App\Models\DoctorInstructionOrder;
use App\Models\Instruction;
use Illuminate\Http\Request;

class InstructionController extends Controller
{
    public function index()
    {
        $doctorId = auth()->id();

        // Fetch all instructions: global or doctor-specific
        $instructions = Instruction::where('is_global', true)
            ->orWhere('doctor_id', $doctorId)
            ->get();

        // Fetch custom order for this doctor
        $orders = DoctorInstructionOrder::where('doctor_id', $doctorId)
            ->pluck('sort_order', 'instruction_id'); // [instruction_id => sort_order]

        // Map sort_order, default to 9999 if not set
        $instructions = $instructions->sortBy(function ($inst) use ($orders) {
            return $orders[$inst->id] ?? 9999;
        })->values();

        return response()->json($instructions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255|unique:instructions,text',
        ]);

        $validated += ['doctor_id' => auth()->id()];

        $instruction = Instruction::create($validated);

        return response()->json($instruction, 201);
    }

    public function update(Request $request, Instruction $instruction)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:255|unique:instructions,text,'.$instruction->id,
        ]);

        $instruction->update($validated);

        return response()->json($instruction);
    }

    public function destroy(Instruction $instruction)
    {
        if ($instruction->doctor_id !== auth()->id() && ! $instruction->is_global) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Also: Should doctors be allowed to delete 'global' instructions? Probably not.
        if ($instruction->is_global) {
            return response()->json(['message' => 'Cannot delete global records'], 403);
        }

        $instruction->delete();

        return response()->json(['message' => 'Instruction deleted successfully']);
    }

    public function reorder(Request $request)
    {
        $doctorId = auth()->id();
        $orderData = $request->input('order'); // array of instruction IDs

        foreach ($orderData as $index => $instructionId) {
            DoctorInstructionOrder::updateOrCreate(
                ['doctor_id' => $doctorId, 'instruction_id' => $instructionId],
                ['sort_order' => $index]
            );
        }

        return response()->json(['success' => true]);
    }
}
