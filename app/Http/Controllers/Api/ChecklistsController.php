<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChecklistsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Initialize
        $data = Checklists::with('user')
            ->where(function ($q) {
                if (request('search')) {
                    $q->where('name', 'LIKE', '%' . request('search') . '%');
                }
            })
            ->where('user_id', auth()->user()->id)
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
    public function store(Request $request)
    {
        // Validation
        $messages = [
            'name.required'             => 'Nama wajib diisi.',
            'name.string'               => 'Nama harus berupa teks.',
            'name.max'                  => 'Nama tidak boleh lebih dari :max karakter.',
            'background_color.regex'    => 'Warna latar belakang harus dalam format HEX yang valid.',
        ];

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'background_color'  => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/'
            ],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'success'   => false,
                'message'   => $validator->errors()->first()
            ], 400);
        }

        try {
            // Transaction DB
            DB::beginTransaction();

            $data = Checklists::create([
                'user_id'           => auth()->user()->id,
                'name'              => $request->name,
                'background_color'  => $request->background_color
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
                'status'    => false,
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
    public function show($id)
    {
        // Check Data
        $data = Checklists::with('items')->where(['id' => $id, 'user_id' => auth()->user()->id])->first();

        if (!$data) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data tidak ditemukan.'
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
    public function update(Request $request, $id)
    {
        // Validation
        $messages = [
            'name.required'             => 'Nama wajib diisi.',
            'name.string'               => 'Nama harus berupa teks.',
            'name.max'                  => 'Nama tidak boleh lebih dari :max karakter.',
            'background_color.regex'    => 'Warna latar belakang harus dalam format HEX yang valid.',
        ];

        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'background_color'  => [
                'nullable',
                'regex:/^#[0-9A-Fa-f]{6}$/'
            ],
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'    => false,
                'message'   => $validator->errors()->first()
            ], 400);
        }

        // Check Data
        $data = Checklists::where(['id' => $id, 'user_id' => auth()->user()->id])->first();

        if (!$data) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data tidak ditemukan.'
            ], 404);
        }

        try {
            // Transaction DB
            DB::beginTransaction();

            $data->update([
                'name'              => $request->name,
                'background_color'  => $request->background_color
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
                'status'    => false,
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
    public function destroy($id)
    {
        // Initialize
        $data = Checklists::where(['id' => $id])->first();

        if (!$data) {
            return response()->json([
                'status'    => false,
                'message'   => 'Data tidak ditemukan.'
            ], 404);
        }

        if ($data->user_id != auth()->user()->id) {
            return response()->json([
                'status'    => false,
                'message'   => 'Anda tidak memiliki akses untuk mengahpus data orang lain.'
            ], 403);
        }

        $data->delete();

        return response()->json([
            'status'    => true,
            'message'   => 'Berhasil menghapus data.',
            'data'      => [
                'id'    => $id,
                'date'  => date('Y-m-d H:i:s')
            ]
        ], 200);
    }
}
