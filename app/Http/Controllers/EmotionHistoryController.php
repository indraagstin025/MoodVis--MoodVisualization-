<?php

namespace App\Http\Controllers;

use App\Models\EmotionRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Pastikan ini di-import
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmotionHistoryController extends Controller
{
    /**
     * Mengambil profile emosi rata-rata dan Emosi ringkasan untuk periode tertentu.
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

        try {
            $validated = $this->validate($request, [
                'period_type' => 'required|string|in:weekly,monthly',
                'date' => 'sometimes|nullable|date_format:Y-m-d',
                'year' => 'sometimes|nullable|integer|min:2000|max:' . (date('Y') + 5),
                'month' => 'sometimes|nullable|integer|min:1|max:12',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
        }

        $periodType = $validated['period_type'];
        $startDate = null;
        $endDate = null;

        try {
            if ($periodType === 'weekly') {
                $targetDate = isset($validated['date']) ? Carbon::parse($validated['date']) : Carbon::now();
                $startDate = $targetDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
                $endDate = $targetDate->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
            } elseif ($periodType === 'monthly') {
                $targetYear = $validated['year'] ?? Carbon::now()->year;
                $targetMonth = $validated['month'] ?? Carbon::now()->month;
                $baseDateForMonth = Carbon::create($targetYear, $targetMonth, 1);
                $startDate = $baseDateForMonth->copy()->startOfMonth()->toDateString();
                $endDate = $baseDateForMonth->copy()->endOfMonth()->toDateString();
            }
        } catch (\Exception $e) {
            Log::error("Error parsing date for emotion summary: " . $e->getMessage(), $validated);
            return response()->json(['message' => 'Format tanggal atau periode tidak valid.'], 400);
        }

        $emotionScoreColumns = [
            'happiness_score' => 'happy',
            'sadness_score'   => 'sad',
            'anger_score'     => 'angry',
            'fear_score'      => 'fearful',
            'disgust_score'   => 'disgusted',
            'surprise_score'  => 'surprised',
            'neutral_score'   => 'neutral',
        ];

        // PERBAIKAN DI SINI: Buat array of strings, bukan array of DB::raw() objects
        $rawSelectStrings = [];
        foreach ($emotionScoreColumns as $dbColumn => $alias) {
            // Membuat string SQL mentah untuk setiap klausa AVG
            $rawSelectStrings[] = "AVG(`{$dbColumn}`) as avg_{$alias}";
        }

        try {
            $averageScoresResult = EmotionRecord::where('user_id', $user->id)
                ->whereBetween('detection_timestamp', [$startDate . " 00:00:00", $endDate . " 23:59:59"])
                // Menggunakan implode pada array string SQL mentah
                ->selectRaw(implode(', ', $rawSelectStrings))
                ->first();

            $averageScores = [];
            $summaryEmotion = 'tidak ada data';
            $maxAvgScore = -1.0;

            if ($averageScoresResult) {
                $hasValidData = false;
                foreach ($emotionScoreColumns as $dbColumn => $alias) {
                    $avgScoreValue = $averageScoresResult->{"avg_{$alias}"};
                    $averageScores[$alias] = null; // Inisialisasi

                    if ($avgScoreValue !== null) {
                        $currentAvgScore = round(floatval($avgScoreValue), 4);
                        $averageScores[$alias] = $currentAvgScore;
                        $hasValidData = true;

                        if ($currentAvgScore > $maxAvgScore) {
                            $maxAvgScore = $currentAvgScore;
                            $summaryEmotion = $alias;
                        }
                    }
                }
                if (!$hasValidData && $averageScoresResult->count() > 0) { // Ada baris hasil tapi semua AVG null
                     $summaryEmotion = 'tidak ada data skor'; // Atau biarkan 'tidak ada data'
                } else if (!$hasValidData) { // Tidak ada baris hasil sama sekali atau semua AVG null
                     $summaryEmotion = 'tidak ada data';
                }

            } else { // Jika $averageScoresResult adalah null (tidak ada record sama sekali)
                foreach ($emotionScoreColumns as $dbColumn => $alias) {
                    $averageScores[$alias] = null;
                }
            }

            return response()->json([
                'period_type' => $periodType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'summary_emotion' => $summaryEmotion,
                'average_scores' => $averageScores,
            ]);

        } catch (\Exception $e) {
            Log::error("Error calculating emotion summary for user {$user->id}: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() , [
                'request' => $request->all(),
                'calculated_period' => ['start' => $startDate, 'end' => $endDate]
            ]);
            return response()->json(['message' => 'Gagal menghitung ringkasan emosi.'], 500);
        }
    }

    // ... (method getEmotionFrequencyTrend Anda yang sudah ada) ...
    // Pastikan untuk menempatkan method getEmotionFrequencyTrend di sini jika belum ada
    public function getEmotionFrequencyTrend(Request $request)
    {
        // ... (Kode lengkap getEmotionFrequencyTrend yang sudah berfungsi)
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $validated = $this->validate($request, [
                'start_date' => 'required|date_format:Y-m-d',
                'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $e->errors()], 422);
        }

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        try {
            $frequencyDataRaw = EmotionRecord::where('user_id', $user->id)
                ->whereBetween('detection_timestamp', [$startDate, $endDate])
                ->select(
                    DB::raw('DATE(detection_timestamp) as detection_day'),
                    'dominant_emotion',
                    DB::raw('COUNT(*) as frequency')
                )
                ->groupBy('detection_day', 'dominant_emotion')
                ->orderBy('detection_day', 'asc')
                ->orderBy('dominant_emotion', 'asc')
                ->get();

            $formattedData = [];
            $tempPivot = [];

            $allEmotionsFromDb = EmotionRecord::where('user_id', $user->id)
                ->whereBetween('detection_timestamp', [$startDate, $endDate])
                ->distinct()
                ->pluck('dominant_emotion')
                ->toArray();

            $allEmotionKeys = array_map(function ($emotion) {
                return strtolower(str_replace(' ', '_', $emotion));
            }, $allEmotionsFromDb);
            $allEmotionKeys = array_unique($allEmotionKeys);

            $currentDayForPivot = $startDate->copy();
            while ($currentDayForPivot->lte($endDate)) {
                $dateString = $currentDayForPivot->toDateString();
                $tempPivot[$dateString] = ['date' => $dateString];
                foreach ($allEmotionKeys as $emotionKey) {
                    $tempPivot[$dateString][$emotionKey] = 0;
                }
                $currentDayForPivot->addDay();
            }

            foreach ($frequencyDataRaw as $record) {
                $date = $record->detection_day;
                $emotionKey = strtolower(str_replace(' ', '_', $record->dominant_emotion));
                $frequency = $record->frequency;

                if (isset($tempPivot[$date])) {
                    $tempPivot[$date][$emotionKey] = $frequency;
                } else {
                    $tempPivot[$date] = ['date' => $date];
                    foreach ($allEmotionKeys as $emKey) {
                        $tempPivot[$date][$emKey] = 0;
                    }
                    $tempPivot[$date][$emotionKey] = $frequency;
                }
            }
            $formattedData = array_values($tempPivot);

            if ($frequencyDataRaw->isEmpty() && empty($allEmotionKeys)) {
                return response()->json([
                    'message' => 'Tidak ada data deteksi emosi ditemukan pada periode yang dipilih.',
                    'pivotData' => [],
                    'chartJsFormat' => [
                        'labels' => [],
                        'datasets' => []
                    ]
                ]);
            }

            $chartJsLabels = [];
            $chartJsDatasets = [];
            $emotionSeries = [];

            if (!empty($formattedData)) {
                foreach ($formattedData as $dataPoint) {
                    $chartJsLabels[] = $dataPoint['date'];
                }

                foreach ($allEmotionKeys as $emotionKey) {
                    $originalEmotionLabel = "";
                    foreach($allEmotionsFromDb as $emFromDb) {
                        if (strtolower(str_replace(' ', '_', $emFromDb)) === $emotionKey) {
                            $originalEmotionLabel = ucfirst($emFromDb);
                            break;
                        }
                    }
                    if(empty($originalEmotionLabel)) $originalEmotionLabel = ucfirst(str_replace('_', ' ', $emotionKey));

                    $emotionSeries[$emotionKey] = [
                        'label' => $originalEmotionLabel,
                        'data' => array_fill(0, count($chartJsLabels), 0)
                    ];
                }

                foreach ($formattedData as $index => $dataPoint) {
                    foreach ($allEmotionKeys as $emotionKey) {
                        if (isset($dataPoint[$emotionKey])) {
                            if (isset($emotionSeries[$emotionKey])) {
                                $emotionSeries[$emotionKey]['data'][$index] = $dataPoint[$emotionKey];
                            }
                        }
                    }
                }
                $chartJsDatasets = array_values($emotionSeries);
            }

            return response()->json([
                'message' => 'Tren frekuensi emosi berhasil diambil.',
                'pivotData' => $formattedData,
                'chartJsFormat' => [
                    'labels' => $chartJsLabels,
                    'datasets' => $chartJsDatasets
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error fetching emotion frequency trend for user {$user->id}: " . $e->getMessage(), [
                'request' => $request->all(),
                'stacktrace' => $e->getTraceAsString()
            ]);
            return response()->json(['message' => 'Gagal mengambil tren frekuensi emosi.'], 500);
        }
    }
}
