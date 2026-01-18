<?php

namespace App\Services;

use App\Repositories\RecipeRepository;
use App\Repositories\TagRepository;
use App\Repositories\IngredientRepository;
use App\Models\Recipe;

class RecipeService
{
    private $recipeRepository;
    private $tagRepository;
    private $ingredientRepository;

    public function __construct()
    {
        $this->recipeRepository = new RecipeRepository();
        $this->tagRepository = new TagRepository();
        $this->ingredientRepository = new IngredientRepository();
    }

    public function getAllRecipes()
    {
        return $this->recipeRepository->findAll();
    }

    public function getRecipeById($recipeId)
    {
        return $this->recipeRepository->getRecipeWithTags($recipeId);
    }

    public function searchByTag($tagId)
    {
        return $this->recipeRepository->findByTag($tagId);
    }

    public function searchByCategory($categoryId)
    {
        return $this->recipeRepository->findByCategory($categoryId);
    }

    public function searchRecipes($keyword)
    {
        return $this->recipeRepository->search($keyword);
    }

    public function createRecipe($title, $description, $instructions, $imageUrl, $prepTime, $cookTime, $servings, $difficulty, $categoryId, $tags = [])
    {
        if (empty($title) || empty($instructions)) {
            throw new \Exception("Title and instructions are required");
        }

        $recipe = new Recipe($title, $description);
        $recipe->setInstructions($instructions);
        $recipe->setImageUrl($imageUrl);
        $recipe->setPrepTime($prepTime);
        $recipe->setCookTime($cookTime);
        $recipe->setServings($servings ?? 1);
        $recipe->setDifficulty($difficulty);
        $recipe->setCategory($categoryId);

        $recipeId = $this->recipeRepository->create($recipe);

        // Add tags to recipe
        foreach ($tags as $tagId) {
            $this->recipeRepository->addTag($recipeId, $tagId);
        }

        return $recipeId;
    }

    public function updateRecipe($recipeId, $title, $description, $instructions, $imageUrl, $prepTime, $cookTime, $servings, $difficulty, $categoryId)
    {
        $recipe = new Recipe($title, $description);
        $recipe->setId($recipeId);
        $recipe->setInstructions($instructions);
        $recipe->setImageUrl($imageUrl);
        $recipe->setPrepTime($prepTime);
        $recipe->setCookTime($cookTime);
        $recipe->setServings($servings);
        $recipe->setDifficulty($difficulty);
        $recipe->setCategory($categoryId);

        return $this->recipeRepository->update($recipe);
    }

    public function deleteRecipe($recipeId)
    {
        return $this->recipeRepository->delete($recipeId);
    }

    public function addTagToRecipe($recipeId, $tagId)
    {
        return $this->recipeRepository->addTag($recipeId, $tagId);
    }

    public function removeTagFromRecipe($recipeId, $tagId)
    {
        return $this->recipeRepository->removeTag($recipeId, $tagId);
    }

    public function addIngredientToRecipe($recipeId, $ingredientId, $quantity, $unit)
    {
        return $this->recipeRepository->addIngredient($recipeId, $ingredientId, $quantity, $unit);
    }

    public function removeIngredientFromRecipe($recipeId, $ingredientId)
    {
        return $this->recipeRepository->removeIngredient($recipeId, $ingredientId);
    }

    public function removeAllIngredientsFromRecipe($recipeId)
    {
        return $this->recipeRepository->removeAllIngredients($recipeId);
    }

    public function getRecipeWithIngredients($recipeId)
    {
        $recipe = $this->getRecipeById($recipeId);
        $ingredients = $this->ingredientRepository->getIngredientsByRecipe($recipeId);
        $recipe['ingredients'] = $ingredients;
        return $recipe;
    }
}
