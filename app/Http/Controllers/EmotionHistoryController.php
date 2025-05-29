<?php

namespace App\Http\Controllers;

use App\Models\EmotionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmotionHistoryController extends Controller
{
    /**
     * Mengambil profile emosi rata rata dan Emosi ringkasan untuk periode tertentu.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function getEmotionSummary(Request $request)
     {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $this->validate($request, [
            'period_type' => 'required|string|in:weekly,monthly',
            'date' => 'sometimes|nullable|date_format:Y-m-d',
            'year' => 'sometimes|nullable|integer|min:2000|max'.(date('Y') + 1),
            'month' => 'sometimes|nullable|integer|min:1|max:12',
        ]);

        $periodType = $validated['period_type'];

        $startDate = null;
        $endDate = null;

        if ($periodType === 'weekly') {
            $targetDate = isset($validated['date']) ? Carbon::parse($validated['date']) : Carbon::now();
            $startDate = $targetDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
            $endDate = $targetDate->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        } elseif ($periodType === 'monthly') {
            $targetYear = $validated['year'] ?? Carbon::now()->year;
            $targetMonth = $validated['month'] ?? Carbon::now()->month;
            $starDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->endOfMonth()->toDateString();
        }

        $emotionScoreColums = [
            'happines_score' => 'happy',
            'sadness_score' => 'angry',
            'anger_score' => 'angry',
            'fear_score' => 'fearful',
            'disgust_score' => 'disgusted',
            'suprises_score' => 'suprised',
            'neutral_score' => 'neutral',
        ];

        $selectClauses = [];
        foreach ($emotionScoreColums as $dbColumn => $alias) {
            $selectClauses[] = DB::raw("AVG('{$dbColumn}') as avg_{$alias}");
        }

        $averageScoresResult = EmotionRecord::where('user_id', $user->id)
            ->whereBetween('detection_timestamp', [$startDate, $endDate])
            ->select($selectClauses)
            ->first
        ();

        $averageScores = [];
        $summaryEmotion = 'tidak ada data';
        $maxAvgScore = -1;

        if ($averageScoresResult) {
            $hasData = false;
            foreach ($emotionScoreColums as $dbColumn => $alias) {
                $avgScore = $averageScoresResult->{"avg_{$alias}"};
                $averageScores[$alias] = $avgScore !== null ? round(floatval($avgScore), 4) : null;

                if ($averageScores[$alias] !== null) {
                    $hasData = true;
                    if ($averageScores[$alias] > $maxAvgScore) {
                        $maxAvgScore = $averageScores[$alias];
                        $summaryEmotion = $alias;
                    }
                }
            }
            if (!$hasData) { // Jika semua skor rata-rata adalah null (tidak ada record sama sekali)
                $summaryEmotion = 'tidak ada data';
            }
        }

        return response()->json([
            'period_type' => $periodType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'summary_emotion' => $summaryEmotion,
            'average_scores' => $averageScores,
        ]);


     }
}


?>
