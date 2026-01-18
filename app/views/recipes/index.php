<?php
$pageTitle = 'Recipes';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <div>
            <h1>🍽️ Recipes</h1>
            <p class="text-muted">Browse and manage your recipes</p>
        </div>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
            <a href="/recipe/create" class="btn btn-success">+ Add New Recipe</a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- Filters Sidebar -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🔍 Filters</h5>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label for="searchInput" class="form-label">Search</label>
                        <input type="text" class="form-control" id="searchInput" name="q" placeholder="Type to search..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label for="categoryFilter" class="form-label">Category</label>
                        <select class="form-select" id="categoryFilter" name="category">
                            <option value="">All Categories</option>
                            <?php if (isset($categories)): ?>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            data-color="<?php echo htmlspecialchars($category['color'] ?? '#6c757d'); ?>"
                                            <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['icon'] ?? ''); ?> <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <button type="button" class="btn btn-secondary w-100" id="clearFilters">Clear Filters</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recipes Grid -->
    <div class="col-md-9">
        <div id="recipesContainer">
            <?php if (isset($recipes) && count($recipes) > 0): ?>
                <div class="row">
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="col-md-4 mb-4 recipe-card" data-recipe-id="<?php echo $recipe['id']; ?>">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($recipe['title']); ?></h5>
                                    
                                    <!-- Category Badges -->
                                    <div class="mb-2">
                                        <?php if (isset($recipe['categories']) && is_array($recipe['categories'])): ?>
                                            <?php foreach ($recipe['categories'] as $category): ?>
                                                <span class="badge me-1 mb-1" style="background-color: <?php echo htmlspecialchars($category['color']); ?>;">
                                                    <?php echo htmlspecialchars($category['icon']); ?> <?php echo htmlspecialchars($category['name']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <p class="card-text"><?php echo htmlspecialchars(substr($recipe['description'] ?? '', 0, 100)); ?>...</p>
                                    <small class="text-muted">
                                        ⏱️ Prep: <?php echo htmlspecialchars($recipe['prep_time'] ?? '0'); ?> min | 
                                        Cook: <?php echo htmlspecialchars($recipe['cook_time'] ?? '0'); ?> min
                                    </small>
                                </div>
                                <div class="card-footer bg-white">
                                    <a href="/recipe/view?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                        <a href="/recipe/edit?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="/recipe/delete?id=<?php echo $recipe['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>No recipes found. <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?><a href="/recipe/create">Create your first recipe!</a><?php endif; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Live search and filtering
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
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
    const categoryValue = categoryFilter.value;

    // Build query string
    const params = new URLSearchParams();
    if (searchValue) params.append('q', searchValue);
    if (categoryValue) params.append('category', categoryValue);

    const queryString = params.toString();
    const url = '/recipe/index' + (queryString ? '?' + queryString : '');

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
            }
        })
        .catch(error => {
            console.error('Error fetching recipes:', error);
        });
}

// Event listeners
searchInput.addEventListener('input', performSearch);
categoryFilter.addEventListener('change', updateRecipes);

clearFiltersBtn.addEventListener('click', () => {
    searchInput.value = '';
    categoryFilter.value = '';
    updateRecipes();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
