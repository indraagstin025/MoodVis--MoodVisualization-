<?php

namespace App\Http\Controllers;

use App\Models\EmotionRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmotionRecordController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request)
    {
        $user = Auth::user();

        $perPage = $request->query('per_page', 15);
        $sortBy = $request->query('sort_by', 'detection_timestamp');
        $sortOrder = $request->query('sort_order', 'desc');

        $allowedSortColumns = [
            'detection_timestamp',
            'dominant_emotion',
            'created_at'

        ];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'detection_timestamp';
        }

        try {
            $emotionRecords = EmotionRecord::where('user_id', $user->id)
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);

            return response()->json($emotionRecords);
        } catch (\Exception $e) {
            Log::error('Error fetching emotion records' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data riwayat Emosi.'], 500);
        }
    }

    /**
     * Meyimpan catatan hasil deteksi emosi yang baru.
     *
     *@param  \Illuminate\Http\Request %request
     *@return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Validasi input dari frontend
        // Pastikan nama field (misal 'happiness_score') sesuai dengan yang dikirim dari frontend
        // dan juga sesuai dengan yang ada di $fillable model EmotionRecord.
        // Saya berasumsi typo 'happines_score' sudah diperbaiki menjadi 'happiness_score'.
        $validatedData = $this->validate($request, [
            'timestamp' => 'required|date_format:Y-m-d\TH:i:s.v\Z', // Format ISO 8601 dari JS Date.toISOString()
            'dominant_emotion' => 'required|string|max:255',
            'happines_score' => 'required|numeric|between:0,1',
            'sadness_score' => 'required|numeric|between:0,1',
            'anger_score' => 'required|numeric|between:0,1',
            'fear_score' => 'required|numeric|between:0,1',
            'disgust_score' => 'required|numeric|between:0,1',
            'surprise_score' => 'required|numeric|between:0,1',
            'neutral_score' => 'required|numeric|between:0,1',
            'age' => 'sometimes|nullable|integer|min:0|max:150',
            'gender' => ['sometimes', 'nullable', 'string', Rule::in(['male', 'female', 'other'])], // Validasi gender
            'gender_probability' => 'sometimes|nullable|numeric|between:0,1',
        ]);

        try {
            $emotionRecord = EmotionRecord::create([
                'user_id' => $user->id,
                'detection_timestamp' => $validatedData['timestamp'], // Data dari frontend 'timestamp' disimpan ke 'detection_timestamp'
                'dominant_emotion' => $validatedData['dominant_emotion'],
                'happines_score' => $validatedData['happines_score'], // <-- pastikan ini benar
                'sadness_score' => $validatedData['sadness_score'],
                'anger_score' => $validatedData['anger_score'],
                'fear_score' => $validatedData['fear_score'],
                'disgust_score' => $validatedData['disgust_score'],
                'surprise_score' => $validatedData['surprise_score'],
                'neutral_score' => $validatedData['neutral_score'],
                'age' => $validatedData['age'] ?? null,
                'gender' => $validatedData['gender'] ?? null,
                'gender_probability' => $validatedData['gender_probability'] ?? null,
            ]);

            return response()->json(['message' => 'Catatan emosi berhasil disimpan!', 'data' => $emotionRecord], 201);
        } catch (\Exception $e) {
            Log::error('Error saving emotion record: ' . $e->getMessage());
            // Memberikan detail error jika dalam mode debug
            $errorDetails = config('app.debug') ? ['error_details' => $e->getMessage(), 'trace' => $e->getTraceAsString()] : [];
            return response()->json(array_merge(['message' => 'Gagal menyimpan catatan emosi.'], $errorDetails), 500);
        }
    }

    /**
     * Menampilkan satu catatan emosi secara spesifik
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = Auth::user();
        try {
            $emotionRecord = EmotionRecord::where('user_id', $user->id)->findOrFail($id);
            return response()->json($emotionRecord);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['meesage' => 'Catatan Emosi tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            Log::error('Error Fetching emotion Record {$id}: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengambil data catatan emosi.'], 500);
        }
    }

    /**
     * Menghapus Catatan Emosi spesifik
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = Auth::user();
        try {
            $emotionRecord = EmotionRecord::where('user_id', $user->id)->findOrFail($id);
            $emotionRecord->delete();
            return response()->json(['message' => 'Catatan emosi berhasil dihapus.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Catatan emosi tidak ditemukan.'], 400);
        } catch (\Exception $e) {
            Log::error("Error deleting emotion record {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus catatan emosi.'], 500);
        }
    }
}
