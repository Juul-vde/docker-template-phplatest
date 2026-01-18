<?php
$pageTitle = isset($recipe) ? $recipe['title'] : 'Recipe';
ob_start();
?>

<div class="mb-4">
    <a href="/recipe/index" class="btn btn-secondary">← Back to Recipes</a>
</div>

<?php if (isset($recipe)): ?>
    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Recipe Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h1>
                    
                    <!-- Categories -->
                    <div class="mb-3">
                        <?php if (isset($recipe['categories']) && is_array($recipe['categories']) && count($recipe['categories']) > 0): ?>
                            <?php foreach ($recipe['categories'] as $category): ?>
                                <span class="badge me-2 mb-2" style="background-color: <?php echo htmlspecialchars($category['color']); ?>; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($category['icon']); ?> <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <p class="lead text-muted"><?php echo htmlspecialchars($recipe['description'] ?? ''); ?></p>

                    <!-- Quick Info -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h5">⏱️ Prep Time</div>
                                <div class="fs-6"><?php echo htmlspecialchars($recipe['prep_time'] ?? '0'); ?> min</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h5">🔥 Cook Time</div>
                                <div class="fs-6"><?php echo htmlspecialchars($recipe['cook_time'] ?? '0'); ?> min</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h5">🍽️ Servings</div>
                                <div class="fs-6"><?php echo htmlspecialchars($recipe['servings'] ?? '1'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 bg-light rounded">
                                <div class="h5">📊 Difficulty</div>
                                <div class="fs-6"><?php echo htmlspecialchars($recipe['difficulty'] ?? 'Unknown'); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Actions -->
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <div class="mb-4">
                            <a href="/recipe/edit?id=<?php echo $recipe['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit Recipe</a>
                            <a href="/recipe/delete?id=<?php echo $recipe['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this recipe?');">🗑️ Delete Recipe</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Instructions -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📝 Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="recipe-instructions">
                        <ul class="list-unstyled">
                            <?php 
                            $instructions = $recipe['instructions'] ?? '';
                            
                            // Split by sentence-ending punctuation and preserve it
                            $sentences = preg_split('/(?<=[.!?])\s+/', trim($instructions), -1, PREG_SPLIT_NO_EMPTY);
                            
                            foreach ($sentences as $sentence):
                                $sentence = trim($sentence);
                                if (!empty($sentence)): ?>
                                    <li class="mb-2">
                                        <span class="text-dark"><?php echo htmlspecialchars($sentence); ?></span>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tags Section (if available) -->
            <?php if (isset($recipe['tags']) && is_array($recipe['tags']) && count($recipe['tags']) > 0): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">🏷️ Tags</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <?php foreach ($recipe['tags'] as $tag): ?>
                                <span class="badge bg-secondary me-2 mb-2"><?php echo htmlspecialchars(is_array($tag) ? $tag['name'] : $tag); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar - Ingredients -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🥘 Ingredients</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($recipe['ingredients']) && count($recipe['ingredients']) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recipe['ingredients'] as $ingredient): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                    <span>
                                        <strong><?php echo htmlspecialchars($ingredient['ingredient_name'] ?? $ingredient['name'] ?? ''); ?></strong>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($ingredient['quantity'] ?? 0); ?> <?php echo htmlspecialchars($ingredient['unit'] ?? ''); ?>
                                        </div>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No ingredients listed.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Add to Weekplanner Card -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="card mt-3">
                    <?php if (isset($recipeInWeekplan) && count($recipeInWeekplan) > 0): ?>
                        <!-- Recipe is already in weekplanner -->
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <strong>✓ In your weekplanner!</strong> 
                                <br><small>Appears on <?php echo count($recipeInWeekplan); ?> day(s) this week.</small>
                            </div>
                            <a href="/weekplanner/index" class="btn btn-primary w-100">📅 View in Weekplanner</a>
                        </div>
                    <?php else: ?>
                        <!-- Recipe not in weekplanner -->
                        <div class="card-body">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addToWeekplanModal">
                                📅 Add to Weekplanner
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-warning">
        <p>Recipe not found.</p>
    </div>
<?php endif; ?>

<style>
    .recipe-instructions {
        line-height: 1.8;
    }
    
    .sticky-top {
        position: sticky;
        top: 20px;
        z-index: 10;
    }
    
    @media (max-width: 768px) {
        .sticky-top {
            position: relative;
            top: auto;
        }
    }
</style>

<!-- Add to Weekplanner Modal -->
<?php if (isset($_SESSION['user_id']) && (!isset($recipeInWeekplan) || count($recipeInWeekplan) == 0)): ?>
<div class="modal fade" id="addToWeekplanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Add to Weekplanner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/weekplanner/addmeal">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="weekplanDay" class="form-label">Select Day</label>
                        <select class="form-select" id="weekplanDay" name="day_of_week" required>
                            <option value="">-- Choose a day --</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="7">Sunday</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="weekplanMealType" class="form-label">Meal Type</label>
                        <select class="form-select" id="weekplanMealType" name="meal_type" required>
                            <option value="breakfast">🌅 Breakfast</option>
                            <option value="lunch" selected>🍽️ Lunch</option>
                            <option value="dinner">🌙 Dinner</option>
                            <option value="snack">🍎 Snack</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="weekplanServings" class="form-label">Servings</label>
                        <input type="number" class="form-control" id="weekplanServings" name="servings" value="1" min="1" max="20" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="recipe_id" value="<?php echo isset($recipe) ? $recipe['id'] : ''; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Weekplanner</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
