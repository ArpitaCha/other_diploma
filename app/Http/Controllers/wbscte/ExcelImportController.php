<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Exception;

class ExcelImportController extends Controller
{
    //
    public function UserImport(Request $request)
    {
       try {
                $request->validate([
                    'file' => 'required'
                ]);

                Excel::import(new UsersImport, $request->file('file'));

                return response()->json(['message' => 'Users imported successfully.']);
        } catch (ValidationException $ve) {
            return response()->json([
                'error' => $ve->getMessage(),
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
