<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

/**
 * 営業日計算サービス
 * 
 * ヘルプデスクの営業日（平日）を考慮した日付計算を行う
 */
class BusinessDayCalculator
{
    /**
     * 日本の祝日データ（2025年）
     * 実際の運用では、より包括的な祝日データベースを使用することを推奨
     */
    private const HOLIDAYS_2025 = [
        '2025-01-01', // 元日
        '2025-01-13', // 成人の日
        '2025-02-11', // 建国記念の日
        '2025-02-23', // 天皇誕生日
        '2025-03-20', // 春分の日
        '2025-04-29', // 昭和の日
        '2025-05-03', // 憲法記念日
        '2025-05-04', // みどりの日
        '2025-05-05', // こどもの日
        '2025-05-06', // こどもの日（振替休日）
        '2025-07-21', // 海の日
        '2025-08-11', // 山の日
        '2025-09-15', // 敬老の日
        '2025-09-16', // 月曜日が祝日のため、火曜日が振替休日
        '2025-09-22', // 秋分の日
        '2025-10-13', // スポーツの日
        '2025-11-03', // 文化の日
        '2025-11-23', // 勤労感謝の日
        '2025-11-24', // 勤労感謝の日（振替休日）
    ];

    /**
     * 営業日の終了時刻（17:30）
     */
    private const BUSINESS_END_TIME = '17:30';

    /**
     * 営業日の開始時刻（9:00）
     */
    private const BUSINESS_START_TIME = '09:00';

    /**
     * 受信日時に基づいて回答期限を計算する
     * 
     * ルール：
     * - 受信日時が当日の17:30までの場合：翌営業日の9:00
     * - 受信日時が当日の17:30以降の場合：翌々営業日の9:00
     * - 土日祝日は営業日外として扱う
     * 
     * @param Carbon $receivedAt 受信日時
     * @return Carbon 回答期限
     */
    public function calculateResponseDeadline(Carbon $receivedAt): Carbon
    {
        // 受信日時を日本時間に変換
        $receivedAt = $receivedAt->setTimezone('Asia/Tokyo');

        // 受信日時の時刻を取得
        $receivedTime = $receivedAt->format('H:i');

        // 受信日時が17:30以前かどうかで判定
        $isBeforeBusinessEnd = $receivedTime <= self::BUSINESS_END_TIME;

        // 基準日を設定（17:30以前なら当日、以降なら翌日）
        $baseDate = $isBeforeBusinessEnd ? $receivedAt->copy() : $receivedAt->copy()->addDay();

        // 翌営業日の9:00を計算
        $nextBusinessDay = $this->getNextBusinessDay($baseDate);

        return $nextBusinessDay->setTimeFromTimeString(self::BUSINESS_START_TIME);
    }

    /**
     * 指定日から次の営業日を取得する
     * 
     * @param Carbon $date 基準日
     * @return Carbon 次の営業日
     */
    public function getNextBusinessDay(Carbon $date): Carbon
    {
        $nextDay = $date->copy()->addDay();

        while (!$this->isBusinessDay($nextDay)) {
            $nextDay->addDay();
        }

        return $nextDay;
    }

    /**
     * 指定日が営業日かどうかを判定する
     * 
     * @param Carbon $date 判定する日付
     * @return bool 営業日の場合true
     */
    public function isBusinessDay(Carbon $date): bool
    {
        // 土日は営業日外
        if ($date->isWeekend()) {
            return false;
        }

        // 祝日は営業日外
        if ($this->isHoliday($date)) {
            return false;
        }

        return true;
    }

    /**
     * 指定日が祝日かどうかを判定する
     * 
     * @param Carbon $date 判定する日付
     * @return bool 祝日の場合true
     */
    public function isHoliday(Carbon $date): bool
    {
        $dateString = $date->format('Y-m-d');
        return in_array($dateString, self::HOLIDAYS_2025, true);
    }

    /**
     * 指定日から指定営業日数後の日付を取得する
     * 
     * @param Carbon $date 基準日
     * @param int $businessDays 営業日数
     * @return Carbon 指定営業日数後の日付
     */
    public function addBusinessDays(Carbon $date, int $businessDays): Carbon
    {
        $result = $date->copy();
        $addedDays = 0;

        while ($addedDays < $businessDays) {
            $result->addDay();
            if ($this->isBusinessDay($result)) {
                $addedDays++;
            }
        }

        return $result;
    }

    /**
     * 指定日から指定営業日数前の日付を取得する
     * 
     * @param Carbon $date 基準日
     * @param int $businessDays 営業日数
     * @return Carbon 指定営業日数前の日付
     */
    public function subBusinessDays(Carbon $date, int $businessDays): Carbon
    {
        $result = $date->copy();
        $subtractedDays = 0;

        while ($subtractedDays < $businessDays) {
            $result->subDay();
            if ($this->isBusinessDay($result)) {
                $subtractedDays++;
            }
        }

        return $result;
    }

    /**
     * 2つの日付間の営業日数を計算する
     * 
     * @param Carbon $startDate 開始日
     * @param Carbon $endDate 終了日
     * @return int 営業日数
     */
    public function getBusinessDaysBetween(Carbon $startDate, Carbon $endDate): int
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->startOfDay();

        if ($start->gt($end)) {
            return 0;
        }

        $businessDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($this->isBusinessDay($current)) {
                $businessDays++;
            }
            $current->addDay();
        }

        return $businessDays;
    }
}
