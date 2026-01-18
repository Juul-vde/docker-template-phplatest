<?php

namespace App\Repositories;

use App\Models\WeeklyPlanItem;
use PDO;

class WeeklyPlanItemRepository extends BaseRepository
{
    protected $table = 'weekly_plan_items';

    public function create(WeeklyPlanItem $item)
    {
        $sql = "INSERT INTO {$this->table} (weekly_plan_id, recipe_id, day_of_week, meal_type, servings) 
                VALUES (:weekly_plan_id, :recipe_id, :day_of_week, :meal_type, :servings)";
        
        $this->execute($sql, [
            ':weekly_plan_id' => $item->getWeeklyPlanId(),
            ':recipe_id' => $item->getRecipeId(),
            ':day_of_week' => $item->getDayOfWeek(),
            ':meal_type' => $item->getMealType(),
            ':servings' => $item->getServings() ?? 1
        ]);

        return $this->db->lastInsertId();
    }

    public function update(WeeklyPlanItem $item)
    {
        $sql = "UPDATE {$this->table} SET day_of_week = :day_of_week, meal_type = :meal_type, servings = :servings WHERE id = :id";
        
        return $this->execute($sql, [
            ':id' => $item->getId(),
            ':day_of_week' => $item->getDayOfWeek(),
            ':meal_type' => $item->getMealType(),
            ':servings' => $item->getServings()
        ])->rowCount() > 0;
    }

    public function findByWeeklyPlanId($weeklyPlanId)
    {
        $sql = "SELECT wpi.*, r.title as recipe_title, r.image_url, r.prep_time, r.cook_time 
                FROM {$this->table} wpi
                LEFT JOIN recipes r ON wpi.recipe_id = r.id
                WHERE wpi.weekly_plan_id = :weekly_plan_id ORDER BY wpi.day_of_week, wpi.meal_type";
        $stmt = $this->execute($sql, [':weekly_plan_id' => $weeklyPlanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByWeeklyPlanAndDay($weeklyPlanId, $dayOfWeek)
    {
        $sql = "SELECT wpi.*, r.title as recipe_title, r.image_url, r.prep_time, r.cook_time 
                FROM {$this->table} wpi
                LEFT JOIN recipes r ON wpi.recipe_id = r.id
                WHERE wpi.weekly_plan_id = :weekly_plan_id AND wpi.day_of_week = :day_of_week 
                ORDER BY wpi.meal_type";
        $stmt = $this->execute($sql, [':weekly_plan_id' => $weeklyPlanId, ':day_of_week' => $dayOfWeek]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteByWeeklyPlanId($weeklyPlanId)
    {
        $sql = "DELETE FROM {$this->table} WHERE weekly_plan_id = :weekly_plan_id";
        return $this->execute($sql, [':weekly_plan_id' => $weeklyPlanId])->rowCount();
    }
}
