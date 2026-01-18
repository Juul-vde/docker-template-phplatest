<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\WeeklyPlanService;
use App\Services\RecipeService;
use App\Services\TagService;
use App\Services\CategoryService;

class weekplannercontroller
{
    private $authService;
    private $weeklyPlanService;
    private $recipeService;
    private $tagService;
    private $categoryService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->weeklyPlanService = new WeeklyPlanService();
        $this->recipeService = new RecipeService();
        $this->tagService = new TagService();
        $this->categoryService = new CategoryService();

        if (!$this->authService->isAuthenticated()) {
            header('Location: /auth/index');
            exit;
        }
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];
        $weeklyPlan = $this->weeklyPlanService->getCurrentWeekPlan($userId);

        if (!$weeklyPlan) {
            $weekStartDate = date('Y-m-d', strtotime('monday this week'));
            $weeklyPlan = new \stdClass();
            $weeklyPlan['id'] = $this->weeklyPlanService->createWeeklyPlan($userId, $weekStartDate, 1);
            $weeklyPlan['week_start_date'] = $weekStartDate;
            $weeklyPlan['number_of_servings'] = 1;
        }

        $mealsData = $this->weeklyPlanService->getWeekPlanWithMeals($weeklyPlan['id']);
        
        // Filter out rows where recipe_id is null (from LEFT JOINs with no meals)
        $meals = array_filter($mealsData, function($meal) {
            return !is_null($meal['recipe_id']);
        });
        $meals = array_values($meals);
        
        $mealsByDay = $this->organizeMealsByDay($mealsData);
        $recipes = $this->recipeService->getAllRecipes();

        include __DIR__ . '/../views/weekplanner/index.php';
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $weekStartDate = $_POST['week_start_date'] ?? null;
            $servings = $_POST['number_of_servings'] ?? 1;

            if (!$weekStartDate) {
                throw new \Exception("Week start date is required");
            }

            // Validate date format (YYYY-MM-DD)
            $dateCheck = \DateTime::createFromFormat('Y-m-d', $weekStartDate);
            if (!$dateCheck || $dateCheck->format('Y-m-d') !== $weekStartDate) {
                throw new \Exception("Invalid date format");
            }

            $weeklyPlanId = $this->weeklyPlanService->createWeeklyPlan($userId, $weekStartDate, $servings);

            $_SESSION['success'] = "Weekly plan created successfully";
            header('Location: /weekplanner/index');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /weekplanner/index');
            exit;
        }
    }

    public function addMeal()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle meal addition
            try {
                $userId = $_SESSION['user_id'];
                $recipeId = $_POST['recipe_id'] ?? null;
                $dayOfWeek = $_POST['day_of_week'] ?? null;
                $mealType = $_POST['meal_type'] ?? 'lunch';
                $servings = $_POST['servings'] ?? 1;

                if (!$recipeId || !$dayOfWeek) {
                    throw new \Exception("Recipe and day are required");
                }

                // Validate day of week (1-7)
                if (!is_numeric($dayOfWeek) || $dayOfWeek < 1 || $dayOfWeek > 7) {
                    throw new \Exception("Invalid day of week");
                }

                // Validate servings
                if (!is_numeric($servings) || $servings < 1 || $servings > 20) {
                    throw new \Exception("Servings must be between 1 and 20");
                }

                $weeklyPlan = $this->weeklyPlanService->getCurrentWeekPlan($userId);
                if (!$weeklyPlan) {
                    $weekStartDate = date('Y-m-d', strtotime('monday this week'));
                    $weeklyPlan = new \stdClass();
                    $weeklyPlan['id'] = $this->weeklyPlanService->createWeeklyPlan($userId, $weekStartDate, 1);
                }

                $this->weeklyPlanService->addMealToDay($weeklyPlan['id'], $recipeId, $dayOfWeek, $mealType, $servings);

                $_SESSION['success'] = "Meal added to weekly plan successfully";
                header('Location: /weekplanner/index');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /weekplanner/addmeal');
                exit;
            }
        } else {
            // Show recipe selection form with filters
            try {
                $search = $_GET['search'] ?? '';
                $categoryId = $_GET['category'] ?? null;

                // Start with all recipes
                $recipes = $this->recipeService->getAllRecipes();

                // Apply category filter
                if ($categoryId) {
                    // Filter by category using new junction table method
                    $recipes = $this->recipeService->searchByCategory($categoryId);
                }

                // Filter by search query (applied to already-filtered results)
                if ($search) {
                    $recipes = array_filter($recipes, function($recipe) use ($search) {
                        $searchLower = strtolower($search);
                        return strpos(strtolower($recipe['title']), $searchLower) !== false ||
                               strpos(strtolower($recipe['description']), $searchLower) !== false;
                    });
                }

                $categories = $this->categoryService->getAllCategories();

                include __DIR__ . '/../views/weekplanner/addmeal.php';
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                header('Location: /weekplanner/index');
                exit;
            }
        }
    }

    public function removeMeal()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            return;
        }

        try {
            $itemId = $_POST['item_id'] ?? null;

            if (!$itemId) {
                throw new \Exception("Item ID is required");
            }

            $this->weeklyPlanService->removeMeal($itemId);

            $_SESSION['success'] = "Meal removed from weekly plan";
            header('Location: /weekplanner/index');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /weekplanner/index');
            exit;
        }
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(400);
            return;
        }

        try {
            $itemId = $_GET['meal_id'] ?? null;

            if (!$itemId) {
                throw new \Exception("Meal ID is required");
            }

            // For now, we'll collect the item data from the weekly plan items
            // This would need a proper repository method to fetch a single item
            $userId = $_SESSION['user_id'];
            $weeklyPlan = $this->weeklyPlanService->getCurrentWeekPlan($userId);

            if (!$weeklyPlan) {
                throw new \Exception("No weekly plan found");
            }

            $mealsData = $this->weeklyPlanService->getWeekPlanWithMeals($weeklyPlan['id']);
            
            // Find the specific meal item
            $mealItem = null;
            foreach ($mealsData as $meal) {
                if ($meal['item_id'] == $itemId) {
                    $mealItem = $meal;
                    break;
                }
            }

            if (!$mealItem) {
                throw new \Exception("Meal not found");
            }

            $categories = $this->categoryService->getAllCategories();
            $tags = $this->tagService->getAllTags();

            include __DIR__ . '/../views/weekplanner/edit.php';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /weekplanner/index');
            exit;
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            return;
        }

        try {
            $itemId = $_POST['item_id'] ?? null;
            $dayOfWeek = $_POST['day_of_week'] ?? null;
            $mealType = $_POST['meal_type'] ?? 'lunch';
            $servings = $_POST['servings'] ?? 1;

            if (!$itemId || !$dayOfWeek) {
                throw new \Exception("Item ID and day are required");
            }

            // Validate day of week (1-7)
            if (!is_numeric($dayOfWeek) || $dayOfWeek < 1 || $dayOfWeek > 7) {
                throw new \Exception("Invalid day of week");
            }

            // Validate servings
            if (!is_numeric($servings) || $servings < 1 || $servings > 20) {
                throw new \Exception("Servings must be between 1 and 20");
            }

            // Update the meal item (recipe_id stays the same, only update day, meal type, and servings)
            $this->weeklyPlanService->updateMeal($itemId, $dayOfWeek, $mealType, $servings);

            $_SESSION['success'] = "Meal updated successfully";
            header('Location: /weekplanner/index');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /weekplanner/index');
            exit;
        }
    }

    public function updateServings()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            return;
        }

        try {
            $weeklyPlanId = $_POST['weekly_plan_id'] ?? null;
            $numberOfServings = $_POST['number_of_servings'] ?? 1;

            if (!$weeklyPlanId) {
                throw new \Exception("Weekly plan ID is required");
            }

            $this->weeklyPlanService->updateNumberOfServings($weeklyPlanId, $numberOfServings);

            $_SESSION['success'] = "Number of servings updated";
            header('Location: /weekplanner/index');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /weekplanner/index');
            exit;
        }
    }

    private function organizeMealsByDay($mealsData)
    {
        $organized = [];

        foreach ($mealsData as $meal) {
            $day = $meal['day_of_week'] ?? null;
            if ($day === null) continue;

            if (!isset($organized[$day])) {
                $organized[$day] = [];
            }

            $organized[$day][] = $meal;
        }

        return $organized;
    }
}
