<?php

namespace App\Services;

use App\Repositories\WeeklyPlanRepository;
use App\Repositories\WeeklyPlanItemRepository;
use App\Models\WeeklyPlan;
use App\Models\WeeklyPlanItem;

class WeeklyPlanService
{
    private $weeklyPlanRepository;
    private $weeklyPlanItemRepository;

    public function __construct()
    {
        $this->weeklyPlanRepository = new WeeklyPlanRepository();
        $this->weeklyPlanItemRepository = new WeeklyPlanItemRepository();
    }

    public function getCurrentWeekPlan($userId)
    {
        return $this->weeklyPlanRepository->findCurrentWeekByUser($userId);
    }

    public function getWeekPlanByDate($userId, $weekStartDate)
    {
        return $this->weeklyPlanRepository->findByUserAndDate($userId, $weekStartDate);
    }

    public function getWeekPlanWithMeals($weeklyPlanId)
    {
        return $this->weeklyPlanRepository->getWeeklyPlanWithMeals($weeklyPlanId);
    }

    public function getUserWeekPlans($userId)
    {
        return $this->weeklyPlanRepository->findByUserId($userId);
    }

    public function createWeeklyPlan($userId, $weekStartDate, $numberOfServings = 1)
    {
        // Check if plan for this week already exists
        $existingPlan = $this->getWeekPlanByDate($userId, $weekStartDate);
        if ($existingPlan) {
            return $existingPlan['id'];
        }

        $weeklyPlan = new WeeklyPlan($userId, $weekStartDate, $numberOfServings);
        return $this->weeklyPlanRepository->create($weeklyPlan);
    }

    public function updateNumberOfServings($weeklyPlanId, $numberOfServings)
    {
        $weeklyPlan = new WeeklyPlan();
        $weeklyPlan->setId($weeklyPlanId);
        $weeklyPlan->setNumberOfServings($numberOfServings);

        return $this->weeklyPlanRepository->update($weeklyPlan);
    }

    public function addMealToDay($weeklyPlanId, $recipeId, $dayOfWeek, $mealType = 'lunch', $servings = 1)
    {
        if ($dayOfWeek < 1 || $dayOfWeek > 7) {
            throw new \Exception("Day of week must be between 1 and 7");
        }

        if (!in_array($mealType, ['breakfast', 'lunch', 'dinner', 'snack'])) {
            throw new \Exception("Invalid meal type");
        }

        $item = new WeeklyPlanItem($weeklyPlanId, $recipeId, $dayOfWeek);
        $item->setMealType($mealType);
        $item->setServings($servings);

        return $this->weeklyPlanItemRepository->create($item);
    }

    public function updateMeal($itemId, $dayOfWeek, $mealType, $servings)
    {
        $item = new WeeklyPlanItem();
        $item->setId($itemId);
        $item->setDayOfWeek($dayOfWeek);
        $item->setMealType($mealType);
        $item->setServings($servings);

        return $this->weeklyPlanItemRepository->update($item);
    }

    public function removeMeal($itemId)
    {
        return $this->weeklyPlanItemRepository->delete($itemId);
    }

    public function getMealsForDay($weeklyPlanId, $dayOfWeek)
    {
        return $this->weeklyPlanItemRepository->findByWeeklyPlanAndDay($weeklyPlanId, $dayOfWeek);
    }

    public function getAllMealsInPlan($weeklyPlanId)
    {
        return $this->weeklyPlanItemRepository->findByWeeklyPlanId($weeklyPlanId);
    }

    public function getDayName($dayOfWeek)
    {
        $days = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        ];
        return $days[$dayOfWeek] ?? 'Unknown';
    }
}
