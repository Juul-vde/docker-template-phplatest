<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\RecipeService;
use App\Services\TagService;
use App\Services\CategoryService;
use App\Services\WeeklyPlanService;

class recipecontroller
{
    private $authService;
    private $recipeService;
    private $tagService;
    private $categoryService;
    private $weeklyPlanService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->recipeService = new RecipeService();
        $this->tagService = new TagService();
        $this->categoryService = new CategoryService();
        $this->weeklyPlanService = new WeeklyPlanService();

        if (!$this->authService->isAuthenticated()) {
            header('Location: /auth/index');
            exit;
        }
    }

    public function index()
    {
        // Get filter parameters
        $searchQuery = $_GET['q'] ?? '';
        $categoryId = $_GET['category'] ?? null;

        // Start with all recipes
        $recipes = $this->recipeService->getAllRecipes();

        // Apply category filter first
        if ($categoryId) {
            $recipes = $this->recipeService->searchByCategory($categoryId);
        }

        // Apply search filter to the already-filtered results
        if ($searchQuery) {
            $recipes = array_filter($recipes, function($recipe) use ($searchQuery) {
                $searchLower = strtolower($searchQuery);
                return strpos(strtolower($recipe['title']), $searchLower) !== false ||
                       strpos(strtolower($recipe['description']), $searchLower) !== false;
            });
        }

        $categories = $this->categoryService->getAllCategories();

        include __DIR__ . '/../views/recipes/index.php';
    }

    public function view()
    {
        $recipeId = $_GET['id'] ?? null;

        if (!$recipeId) {
            header('Location: /recipe/index');
            exit;
        }

        $recipe = $this->recipeService->getRecipeWithIngredients($recipeId);

        if (!$recipe) {
            $_SESSION['error'] = "Recipe not found";
            header('Location: /recipe/index');
            exit;
        }

        // Check if recipe is already in current week's plan
        $recipeInWeekplan = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $weeklyPlan = $this->weeklyPlanService->getCurrentWeekPlan($userId);
            
            if ($weeklyPlan) {
                $mealsData = $this->weeklyPlanService->getWeekPlanWithMeals($weeklyPlan['id']);
                // Find meals with this recipe
                $recipeInWeekplan = array_filter($mealsData, function($meal) use ($recipeId) {
                    return $meal['recipe_id'] == $recipeId;
                });
            }
        }
        
        include __DIR__ . '/../views/recipes/view.php';
    }

    public function search()
    {
        $keyword = $_GET['q'] ?? '';
        $tagId = $_GET['tag'] ?? null;

        if ($tagId) {
            $recipes = $this->recipeService->searchByTag($tagId);
        } elseif ($keyword) {
            $recipes = $this->recipeService->searchRecipes($keyword);
        } else {
            $recipes = $this->recipeService->getAllRecipes();
        }

        $tags = $this->tagService->getAllTags();

        include __DIR__ . '/../views/recipes/search.php';
    }

    public function create()
    {
        try {
            $this->authService->requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = "You don't have permission to create recipes";
            header('Location: /recipe/index');
            exit;
        }

        $tags = $this->tagService->getAllTags();
        $commonTags = $this->tagService->getCommonTags();

        include __DIR__ . '/../views/recipes/create.php';
    }

    public function handleCreate()
    {
        try {
            $this->authService->requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = "You don't have permission to create recipes";
            header('Location: /recipe/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /recipe/create');
            exit;
        }

        try {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $instructions = $_POST['instructions'] ?? '';
            $imageUrl = $_POST['image_url'] ?? '';
            $prepTime = $_POST['prep_time'] ?? 0;
            $cookTime = $_POST['cook_time'] ?? 0;
            $servings = $_POST['servings'] ?? 1;
            $difficulty = $_POST['difficulty'] ?? 'medium';
            $categoryId = $_POST['category_id'] ?? null;
            $tags = $_POST['tags'] ?? [];
            $ingredients = $_POST['ingredients'] ?? []; // Array of ingredient data

            // Validate required fields
            if (empty($title)) {
                throw new \Exception("Recipe title is required");
            }

            if (empty($instructions)) {
                throw new \Exception("Instructions are required");
            }

            // Create recipe
            $recipeId = $this->recipeService->createRecipe(
                $title,
                $description,
                $instructions,
                $imageUrl,
                $prepTime,
                $cookTime,
                $servings,
                $difficulty,
                $categoryId,
                $tags
            );

            // Add ingredients to recipe
            if (!empty($ingredients)) {
                foreach ($ingredients as $ingredient) {
                    if (isset($ingredient['id']) && isset($ingredient['quantity']) && isset($ingredient['unit'])) {
                        $this->recipeService->addIngredientToRecipe(
                            $recipeId,
                            $ingredient['id'],
                            $ingredient['quantity'],
                            $ingredient['unit']
                        );
                    }
                }
            }

            $_SESSION['success'] = "Recipe created successfully";
            header('Location: /recipe/view?id=' . $recipeId);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /recipe/create');
            exit;
        }
    }

    public function store()
    {
        // Alias for handleCreate to support both naming conventions
        $this->handleCreate();
    }

    public function edit()
    {
        try {
            $this->authService->requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = "You don't have permission to edit recipes";
            header('Location: /recipe/index');
            exit;
        }

        $recipeId = $_GET['id'] ?? null;

        if (!$recipeId) {
            header('Location: /recipe/index');
            exit;
        }

        $recipe = $this->recipeService->getRecipeWithIngredients($recipeId);

        if (!$recipe) {
            $_SESSION['error'] = "Recipe not found";
            header('Location: /recipe/index');
            exit;
        }

        $tags = $this->tagService->getAllTags();
        $commonTags = $this->tagService->getCommonTags();

        include __DIR__ . '/../views/recipes/edit.php';
    }

    public function handleEdit()
    {
        try {
            $this->authService->requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = "You don't have permission to edit recipes";
            header('Location: /recipe/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /recipe/index');
            exit;
        }

        try {
            $recipeId = $_POST['recipe_id'] ?? null;
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $instructions = $_POST['instructions'] ?? '';
            $imageUrl = $_POST['image_url'] ?? '';
            $prepTime = $_POST['prep_time'] ?? 0;
            $cookTime = $_POST['cook_time'] ?? 0;
            $servings = $_POST['servings'] ?? 1;
            $difficulty = $_POST['difficulty'] ?? 'medium';
            $categoryId = $_POST['category_id'] ?? null;
            $tags = $_POST['tags'] ?? [];
            $ingredients = $_POST['ingredients'] ?? [];

            if (!$recipeId) {
                throw new \Exception("Recipe ID is required");
            }

            // Validate required fields
            if (empty($title)) {
                throw new \Exception("Recipe title is required");
            }

            if (empty($instructions)) {
                throw new \Exception("Instructions are required");
            }

            // Update recipe basic info
            $this->recipeService->updateRecipe(
                $recipeId,
                $title,
                $description,
                $instructions,
                $imageUrl,
                $prepTime,
                $cookTime,
                $servings,
                $difficulty,
                $categoryId
            );

            // Update tags - remove all and re-add
            $currentRecipe = $this->recipeService->getRecipeById($recipeId);
            if (isset($currentRecipe['tags'])) {
                foreach (explode(',', $currentRecipe['tags']) as $tagId) {
                    $this->recipeService->removeTagFromRecipe($recipeId, trim($tagId));
                }
            }
            
            foreach ($tags as $tagId) {
                $this->recipeService->addTagToRecipe($recipeId, $tagId);
            }

            // Update ingredients - remove all and re-add
            $this->recipeService->removeAllIngredientsFromRecipe($recipeId);
            
            if (!empty($ingredients)) {
                foreach ($ingredients as $ingredient) {
                    if (isset($ingredient['id']) && isset($ingredient['quantity']) && isset($ingredient['unit'])) {
                        $this->recipeService->addIngredientToRecipe(
                            $recipeId,
                            $ingredient['id'],
                            $ingredient['quantity'],
                            $ingredient['unit']
                        );
                    }
                }
            }

            $_SESSION['success'] = "Recipe updated successfully";
            header('Location: /recipe/view?id=' . $recipeId);
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /recipe/index');
            exit;
        }
    }

    public function update()
    {
        // Alias for handleEdit to support both naming conventions
        $this->handleEdit();
    }

    public function delete()
    {
        try {
            $this->authService->requireAdmin();
        } catch (\Exception $e) {
            $_SESSION['error'] = "You don't have permission to delete recipes";
            header('Location: /recipe/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            return;
        }

        try {
            $recipeId = $_POST['recipe_id'] ?? null;

            if (!$recipeId) {
                throw new \Exception("Recipe ID is required");
            }

            $this->recipeService->deleteRecipe($recipeId);

            $_SESSION['success'] = "Recipe deleted successfully";
            header('Location: /recipe/index');
            exit;
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: /recipe/index');
            exit;
        }
    }
}
