<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklists;
use App\Models\Items;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TodoItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id)
    {
        // Initialize
        $data = Items::with('checklists')
            ->whereHas('checklists', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->where(function ($q) {
                if (request('search')) {
                    $q->where('todo_name', 'LIKE', '%' . request('search') . '%');
                }
            })
            ->where('checklist_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data->items(),
            'meta'      => [
                'current_page'      => $data->currentPage(),
                'from'              => 1,
                'last_page'         => $data->lastPage(),
                'next_page_url'     => $data->nextPageUrl(),
                'path'              => $request->fullUrl(),
                'per_page'          => $data->perPage(),
                'prev_page_url'     => $data->previousPageUrl(),
                'total'             => $data->total()
            ]
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'itemName' => 'required|string|max:255',
        ], [
            'itemName.required' => 'Nama item wajib diisi.',
            'itemName.string'   => 'Nama item harus berupa teks.',
            'itemName.max'      => 'Nama item tidak boleh lebih dari :max karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first()
            ], 400);
        }

        // Check Data
        $checklist = Checklists::where(['id' => $id, 'user_id' => auth()->user()->id])->first();

        if (!$checklist) {
            return response()->json([
                'status'   => false,
                'message'   => 'Data Checklist tidak ditemukan.'
            ], 404);
        }

        try {
            // Transaction DB
            DB::beginTransaction();

            $data = Items::create([
                'checklist_id'  => $id,
                'todo_name'     => $request->itemName,
                'status'        => 0
            ]);

            DB::commit();

            return response()->json([
                'status'    => true,
                'message'   => 'Berhasil menambahkan data.',
                'data'      => $data
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'status'   => false,
                'message'   => '500 Internal Server Error.'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, $itemId)
    {
        // Initialize
        $data = Items::with('checklists')
            ->whereHas('checklists', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->where('checklist_id', $id)
            ->first();

        if (!$data) {
            return response()->json([
                'status'   => false,
                'message'   => 'Data Checklist tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mendapatkan data.',
            'data'      => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $itemId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'itemName' => 'required|string|max:255',
        ], [
            'itemName.required' => 'Nama item wajib diisi.',
            'itemName.string'   => 'Nama item harus berupa teks.',
            'itemName.max'      => 'Nama item tidak boleh lebih dari :max karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first()
            ], 400);
        }

        // Check Data
        $data = Items::with('checklists')
            // ->whereHas('checklists', function ($query) {
            //     $query->where('user_id', auth()->user()->id);
            // })
            ->where('checklist_id', $id)
            ->where('id', $itemId)
            ->first();

        if (!$data) {
            return response()->json([
                'success'   => false,
                'message'   => 'Data Todo Item tidak ditemukan.'
            ], 404);
        }

        try {
            // Transaction DB
            DB::beginTransaction();


            $data->update([
                'checklist_id'  => $id,
                'todo_name'     => $request->itemName,
                'status'        => 0
            ]);

            DB::commit();

            return response()->json([
                'status'    => true,
                'message'   => 'Berhasil mengubah data.',
                'data'      => $data
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();

            return response()->json([
                'success'   => false,
                'message'   => '500 Internal Server Error.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $itemId)
    {
        // Initialize
        $data = Items::with('checklists')
            ->whereHas('checklists', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->where('checklist_id', $id)
            ->where('id', $itemId)
            ->first();

        if (!$data) {
            return response()->json([
                'status'   => false,
                'message'   => 'Data Item tidak ditemukan.'
            ], 404);
        }

        $data->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $itemId,
                'date'  => date('Y-m-d H:i:s')
            ]
        ], 200);
    }

    public function updateStatus(Request $request, $id, $itemId)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:0,1',
        ], [
            'status.required' => 'Nama item wajib diisi.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first()
            ], 400);
        }

        // Initialize
        $data = Items::with('checklists')
            // ->whereHas('checklists', function ($query) {
            // $query->where('user_id', auth()->user()->id);
            // })
            ->where('checklist_id', $id)
            ->where('id', $itemId)
            ->first();

        if (!$data) {
            return response()->json([
                'status'   => false,
                'message'   => 'Data Item tidak ditemukan.'
            ], 404);
        }

        $data->update(['status' => $request->status]);

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil mengubah status.',
            'data'      => $data
        ], 200);
    }
}
