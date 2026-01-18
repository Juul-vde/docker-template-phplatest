<?php
$pageTitle = 'Add Meal to Week Plan';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1>📅 Add Meal to Week Plan</h1>
        <p class="text-muted">Select a recipe and assign it to a specific day and meal type</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Filters Section -->
        <div class="card">
            <div class="card-header">
                <h5>🔍 Filter Recipes</h5>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Recipe name..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php if (isset($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <button type="button" class="btn btn-secondary w-100" id="clearFilters">Clear Filters</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <!-- Recipe Selection -->
        <div class="card">
            <div class="card-header">
                <h5>Available Recipes (<?php echo isset($recipes) ? count($recipes) : 0; ?>)</h5>
            </div>
            <div class="card-body">
                <div id="recipesContainer">
                    <?php if (isset($recipes) && count($recipes) > 0): ?>
                        <div class="row">
                            <?php foreach ($recipes as $recipe): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card recipe-card" data-recipe-id="<?php echo $recipe['id']; ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h6>
                                            <p class="card-text small text-muted"><?php echo htmlspecialchars(substr($recipe['description'] ?? '', 0, 80)); ?>...</p>
                                            <div class="mb-2">
                                                <?php if (isset($recipe['categories']) && is_array($recipe['categories'])): ?>
                                                    <?php foreach ($recipe['categories'] as $category): ?>
                                                        <span class="badge me-1 mb-1" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                                                            <?php echo htmlspecialchars($category['icon']); ?> <?php echo htmlspecialchars($category['name']); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-2">
                                                <small>⏱️ Prep: <?php echo htmlspecialchars($recipe['prep_time'] ?? 0); ?>m | Cook: <?php echo htmlspecialchars($recipe['cook_time'] ?? 0); ?>m</small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-sm btn-success select-recipe" data-recipe-id="<?php echo $recipe['id']; ?>" data-recipe-title="<?php echo htmlspecialchars($recipe['title']); ?>">
                                                    ✓ Select
                                                </button>
                                                <a href="/recipe/view?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                    👁️ View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p>No recipes match your filters. Try adjusting them!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for meal assignment -->
<div class="modal fade" id="mealAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📅 Add to Weekplanner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/weekplanner/addmeal">
                <div class="modal-body">
                    <input type="hidden" name="recipe_id" id="modalRecipeId" value="">
                    
                    <div class="mb-3">
                        <label for="dayOfWeek" class="form-label">Select Day</label>
                        <select class="form-select" id="dayOfWeek" name="day_of_week" required>
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
                        <label for="mealType" class="form-label">Meal Type</label>
                        <select class="form-select" id="mealType" name="meal_type" required>
                            <option value="breakfast">🌅 Breakfast</option>
                            <option value="lunch" selected>🍽️ Lunch</option>
                            <option value="dinner">🌙 Dinner</option>
                            <option value="snack">🍎 Snack</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="servings" class="form-label">Servings</label>
                        <input type="number" class="form-control" id="servings" name="servings" value="1" min="1" max="20" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add to Weekplanner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('mealAssignmentModal'));
    const searchInput = document.getElementById('search');
    const categorySelect = document.getElementById('category');
    const clearFiltersBtn = document.getElementById('clearFilters');
    const recipesContainer = document.getElementById('recipesContainer');
    let searchTimeout;
    
    // Debounced search function
    function performSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            updateRecipes();
        }, 300); // Wait 300ms after user stops typing
    }

    // Update recipes based on filters
    function updateRecipes() {
        const searchValue = searchInput.value;
        const categoryValue = categorySelect.value;

        // Build query string
        const params = new URLSearchParams();
        if (searchValue) params.append('search', searchValue);
        if (categoryValue) params.append('category', categoryValue);

        const queryString = params.toString();
        const url = '/weekplanner/addmeal' + (queryString ? '?' + queryString : '');

        // Fetch filtered recipes
        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Parse the HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContainer = doc.getElementById('recipesContainer');
                
                if (newContainer) {
                    recipesContainer.innerHTML = newContainer.innerHTML;
                    // Reattach event listeners to new select buttons
                    attachSelectButtonListeners();
                }
            })
            .catch(error => {
                console.error('Error fetching recipes:', error);
            });
    }

    // Attach event listeners to select buttons
    function attachSelectButtonListeners() {
        document.querySelectorAll('.select-recipe').forEach(btn => {
            btn.addEventListener('click', function() {
                const recipeId = this.dataset.recipeId;
                
                document.getElementById('modalRecipeId').value = recipeId;
                
                // Reset form fields
                document.getElementById('dayOfWeek').value = '';
                document.getElementById('mealType').value = 'lunch';
                document.getElementById('servings').value = '1';
                
                modal.show();
            });
        });
    }

    // Event listeners
    searchInput.addEventListener('input', performSearch);
    categorySelect.addEventListener('change', updateRecipes);

    clearFiltersBtn.addEventListener('click', () => {
        searchInput.value = '';
        categorySelect.value = '';
        updateRecipes();
    });

    // Initial attachment of select button listeners
    attachSelectButtonListeners();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
