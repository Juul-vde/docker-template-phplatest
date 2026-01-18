<?php
$pageTitle = 'Week Planner';
ob_start();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1>📅 Week Planner</h1>
        <p class="text-muted">Plan your meals for the week ahead</p>
    </div>
</div>

<?php if (!isset($weeklyPlan) || !$weeklyPlan): ?>
    <div class="alert alert-info">
        <p>No weekly plan exists yet. Create one to get started!</p>
        <a href="/weekplanner/create" class="btn btn-primary">Create Weekly Plan</a>
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Week of <?php echo htmlspecialchars($weeklyPlan['week_start_date'] ?? ''); ?></h5>
                </div>
                <div class="card-body">
                    <a href="/weekplanner/addmeal" class="btn btn-success">Add Meal</a>
                    <a href="/shoppinglist/index" class="btn btn-info">Generate Shopping List</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <h4>Planned Meals</h4>
            <?php if (isset($meals) && count($meals) > 0): ?>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Meal Type</th>
                            <th>Recipe</th>
                            <th>Servings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($meals as $meal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($meal['day_of_week'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($meal['meal_type'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($meal['recipe_title'] ?? 'Unknown'); ?></td>
                                <td><?php echo htmlspecialchars($meal['servings'] ?? 1); ?></td>
                                <td>
                                    <a href="/weekplanner/edit?meal_id=<?php echo htmlspecialchars($meal['item_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="POST" action="/weekplanner/removemeal" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($meal['item_id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove this meal?');">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No meals planned yet.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/base.php';
?>
