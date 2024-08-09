<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Exception;

class NoteController extends Controller
{
    private $notesModel;
    public function __construct(Note $model ){
        $this->notesModel = $model;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return  $this->notesModel->with('user')->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NoteRequest $request)
    {
        try{
            $note = $this->notesModel->create([ 'user_id' => Auth::user()->id ] + $request->validated());
            return response()->json([
                'note' => $note,
                'message' => 'Note created successfully'
            ], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $note = $this->notesModel->find($id);
        if(!$note){
            return response()->json(['error' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        return $note;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NoteRequest $request, string $id)
    {
        $note = $this->notesModel->find($id);
        if(!$note){
            return response()->json(['error' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        try{
            $note->update($request->validated());
            return response()->json($note, Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $note = $this->notesModel->find($id);
        if(!$note){
            return response()->json(['error' => 'Note not found'], Response::HTTP_NOT_FOUND);
        }
        try{
            $note->delete();
            return response()->json(['message' => 'Note succesfully deleted'], Response::HTTP_OK);
        }catch(Exception $e){
            return response()->json(['error' => 'Internal Server Error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
