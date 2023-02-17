<?php

namespace App\Merryweather\Admin;

use App\Entity\Distribution;

class Month
{
    /** @var mixed[]  */
    private array $weeks = [];
    private string $month;
    private string $year;

    /**
     * @paramn it $monthOffset
     * @param Distribution[] $distributions
     * @throws \Exception
     */
    public function __construct(int $monthOffset = 0, array $distributions = [])
    {
        $distributionByDay = [];
        $today = new \DateTimeImmutable('now');
        foreach ($distributions as $distribution) {
            $key = $distribution->getActiveTill()?->format('ymd');
            if (!isset($distributionByDay[$key])) {
                $distributionByDay[$key] = [];
            }
            $distributionByDay[$key][] = $distribution;
        }

        $firstDayOfMonth = new \DateTimeImmutable('first day of this month');
        if ($monthOffset < 0) {
            $firstDayOfMonth = $firstDayOfMonth->sub(new \DateInterval(sprintf('P%dM', $monthOffset * -1)));
        } elseif ($monthOffset > 0) {
            $firstDayOfMonth = $firstDayOfMonth->add(new \DateInterval(sprintf('P%dM', $monthOffset)));
        }
        $firstDay = $firstDayOfMonth->format('N');
        $this->month = $firstDayOfMonth->format('M');
        $this->year = $firstDayOfMonth->format('Y');

        $targetMonth = $firstDayOfMonth->format('Ym');
        $currentDay = $firstDayOfMonth->sub(new \DateInterval(sprintf('P%dD', $firstDay - 1)));
        $currentMonth = $currentDay->format('Ym');
        $oneDay = new \DateInterval('P1D');
        while ($currentMonth <= $targetMonth) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $distributionsForDay = $distributionByDay[$currentDay->format('ymd')] ?? null;
                $currentMonth = $currentDay->format('Ym');
                $week[] = [
                    'date' => $currentDay,
                    'active' => $currentMonth === $targetMonth,
                    'distributions' => $distributionsForDay,
                    'current' => $currentDay->format('ymd') === $today->format('ymd'),
                    'past' => $currentDay->format('ymd') < $today->format('ymd')
                ];
                $currentDay = $currentDay->add($oneDay);
            }
            $this->weeks[] = $week;
        }
    }

    public function getMonth(): string
    {
        return $this->month;
    }

    /**
     * @return mixed[]
     */
    public function getWeeks(): array
    {
        return $this->weeks;
    }

    public function getYear(): string
    {
        return $this->year;
    }
}
