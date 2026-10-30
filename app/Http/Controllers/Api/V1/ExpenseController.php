<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;

#[Prefix('v1/expenses')]
#[Middleware('auth:sanctum')]
class ExpenseController extends Controller
{
    #[Put('{expense}', name: 'v1.expenses.update', middleware: 'can:update,expense')]
    public function update(UpdateExpenseRequest $request, Expense $expense): ExpenseResource
    {
        $expense->update($request->validated());

        return ExpenseResource::make($expense->load(['category', 'user']));
    }

    #[Delete('{expense}', name: 'v1.expenses.destroy', middleware: 'can:delete,expense')]
    public function destroy(Expense $expense): Response
    {
        $expense->delete();

        return response()->noContent();
    }
}
