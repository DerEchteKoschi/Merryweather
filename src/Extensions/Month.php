<?php

namespace App\Extensions;

use App\Entity\Distribution;

class Month
{
    public string $start = '';
    /** @var mixed[]  */
    private array $weeks = [];
    private string $month;
    private string $year;

    /**
     * @param int $monthOffset
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

        $firstDayOfMonth = \DateTimeImmutable::createFromFormat('U', ''.strtotime('first day of this month'));
        if ($monthOffset < 0) {
            $firstDayOfMonth = $firstDayOfMonth->sub(new \DateInterval(sprintf('P%dM', $monthOffset * -1)));
        } elseif ($monthOffset > 0) {
            $firstDayOfMonth = $firstDayOfMonth->add(new \DateInterval(sprintf('P%dM', $monthOffset)));
        }
        $firstDay = $firstDayOfMonth->format('N');
        $this->month = $firstDayOfMonth->format('M');
        $this->year = $firstDayOfMonth->format('Y');

        $this->start = $firstDayOfMonth->sub(new \DateInterval(sprintf('P%dD', $firstDay - 1)))->format('d.m.Y');

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
