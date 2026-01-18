-- FoodPrep Database Schema
-- Food Preparation Web Application
-- Tags are implemented via a junction table for scalability and reusability
-- Weekly meal planning with automatic shopping list generation

-- ===========================
-- USERS TABLE
-- ===========================
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_photo VARCHAR(255),
    dietary_preferences TEXT,
    allergies TEXT,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (email)
);

-- ===========================
-- CATEGORIES TABLE
-- ===========================
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(255),
    color VARCHAR(50) DEFAULT '#6c757d',
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (display_order)
);

-- ===========================
-- TAGS TABLE (for meal tagging)
-- ===========================
CREATE TABLE IF NOT EXISTS tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================
-- RECIPES/MEALS TABLE
-- ===========================
CREATE TABLE IF NOT EXISTS recipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    image_url VARCHAR(255),
    prep_time INT COMMENT 'in minutes',
    cook_time INT COMMENT 'in minutes',
    servings INT DEFAULT 1,
    difficulty VARCHAR(50),
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX (category_id),
    INDEX (created_at)
);

-- ===========================
-- RECIPE_TAGS JUNCTION TABLE
-- ===========================
-- This junction table implements the many-to-many relationship
-- A recipe can have multiple tags, and tags are reusable across recipes
CREATE TABLE IF NOT EXISTS recipe_tags (
    recipe_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (recipe_id, tag_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- ===========================
-- RECIPE_CATEGORIES JUNCTION TABLE
-- ===========================
-- This junction table implements the many-to-many relationship
-- A recipe can have multiple categories, and categories are reusable across recipes
CREATE TABLE IF NOT EXISTS recipe_categories (
    recipe_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (recipe_id, category_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- ===========================
-- INGREDIENTS TABLE
-- ===========================
CREATE TABLE IF NOT EXISTS ingredients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    calories DECIMAL(8, 2),
    protein DECIMAL(8, 2) COMMENT 'in grams',
    carbs DECIMAL(8, 2) COMMENT 'in grams',
    fat DECIMAL(8, 2) COMMENT 'in grams',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================
-- RECIPE_INGREDIENTS JUNCTION TABLE
-- ===========================
-- This junction table implements the many-to-many relationship
-- A recipe can use multiple ingredients, and ingredients can be used in multiple recipes
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    recipe_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    PRIMARY KEY (recipe_id, ingredient_id),
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE
);

-- ===========================
-- WEEKLY_PLANS TABLE
-- ===========================
-- Stores each user's weekly meal plans
CREATE TABLE IF NOT EXISTS weekly_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    week_start_date DATE NOT NULL,
    number_of_servings INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (week_start_date),
    UNIQUE KEY unique_user_week (user_id, week_start_date)
);

-- ===========================
-- WEEKLY_PLAN_ITEMS TABLE
-- ===========================
-- Links meals to specific days in the weekly plan
CREATE TABLE IF NOT EXISTS weekly_plan_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    weekly_plan_id INT NOT NULL,
    recipe_id INT NOT NULL,
    day_of_week INT NOT NULL COMMENT '1=Monday, 7=Sunday',
    meal_type VARCHAR(50) COMMENT 'breakfast, lunch, dinner, snack',
    servings INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (weekly_plan_id) REFERENCES weekly_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE,
    INDEX (weekly_plan_id),
    INDEX (recipe_id)
);

-- ===========================
-- SHOPPING_LISTS TABLE
-- ===========================
-- Generated shopping lists based on weekly meal plans
CREATE TABLE IF NOT EXISTS shopping_lists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    weekly_plan_id INT NOT NULL,
    generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (weekly_plan_id) REFERENCES weekly_plans(id) ON DELETE CASCADE,
    INDEX (user_id),
    INDEX (weekly_plan_id)
);

-- ===========================
-- SHOPPING_LIST_ITEMS TABLE
-- ===========================
-- Individual items in the shopping list
CREATE TABLE IF NOT EXISTS shopping_list_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    shopping_list_id INT NOT NULL,
    ingredient_id INT NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    is_checked BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shopping_list_id) REFERENCES shopping_lists(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(id) ON DELETE CASCADE,
    INDEX (shopping_list_id),
    INDEX (ingredient_id)
);

-- ===========================
-- SAMPLE DATA - INGREDIENTS
-- ===========================
-- Note: This data has been AI-generated for testing purposes

INSERT INTO ingredients (name, calories, protein, carbs, fat) VALUES
-- ===========================
-- VEGETABLES
-- ===========================
('Onion', 40, 1.1, 9.3, 0.1),
('Garlic', 149, 6.4, 33.1, 0.5),
('Bell Pepper', 31, 1.0, 6.0, 0.3),
('Tomato', 18, 0.9, 3.9, 0.2),
('Cucumber', 16, 0.7, 3.6, 0.1),
('Carrot', 41, 0.9, 9.6, 0.2),
('Broccoli', 34, 2.8, 6.6, 0.4),
('Cauliflower', 25, 1.9, 5.0, 0.3),
('Spinach', 23, 2.9, 3.6, 0.4),
('Zucchini', 17, 1.2, 3.1, 0.3),
('Mushrooms', 22, 3.1, 3.3, 0.3),
('Eggplant', 25, 1.0, 6.0, 0.2),

-- ===========================
-- FRUIT
-- ===========================
('Apple', 52, 0.3, 14.0, 0.2),
('Banana', 89, 1.1, 22.8, 0.3),
('Lemon', 29, 1.1, 9.3, 0.3),
('Avocado', 160, 2.0, 8.5, 14.7),
('Strawberries', 32, 0.7, 7.7, 0.3),

-- ===========================
-- GRAINS & RICE
-- ===========================
('White Rice', 130, 2.7, 28.0, 0.3),
('Brown Rice', 123, 2.7, 25.6, 1.0),
('Pasta', 131, 5.0, 25.0, 1.1),
('Whole Wheat Pasta', 124, 5.5, 26.0, 1.2),
('Quinoa', 120, 4.4, 21.3, 1.9),
('Couscous', 112, 3.8, 23.2, 0.2),
('Oatmeal', 389, 16.9, 66.3, 6.9),

-- ===========================
-- MEAT & FISH
-- ===========================
('Chicken Breast', 165, 31.0, 0.0, 3.6),
('Ground Beef', 250, 26.0, 0.0, 20.0),
('Pork Loin', 143, 26.0, 0.0, 3.5),
('Salmon', 208, 20.0, 0.0, 13.0),
('Canned Tuna', 116, 26.0, 0.0, 1.0),
('Shrimp', 99, 24.0, 0.2, 0.3),

-- ===========================
-- PLANT-BASED PROTEINS
-- ===========================
('Tofu', 76, 8.0, 1.9, 4.8),
('Tempeh', 193, 20.0, 9.0, 11.0),
('Chickpeas', 164, 8.9, 27.4, 2.6),
('Lentils', 116, 9.0, 20.0, 0.4),
('Black Beans', 132, 8.9, 23.7, 0.5),

-- ===========================
-- DAIRY & EGGS
-- ===========================
('Milk', 42, 3.4, 5.0, 1.0),
('Full-fat Yogurt', 61, 3.5, 4.7, 3.3),
('Low-fat Quark', 59, 10.0, 3.9, 0.4),
('Cheese', 402, 25.0, 1.3, 33.0),
('Egg', 155, 13.0, 1.1, 11.0),
('Butter', 717, 0.9, 0.1, 81.0),

-- ===========================
-- OILS & FATS
-- ===========================
('Olive Oil', 884, 0.0, 0.0, 100.0),
('Sunflower Oil', 884, 0.0, 0.0, 100.0),
('Coconut Oil', 862, 0.0, 0.0, 100.0),

-- ===========================
-- SPICES & SAUCES
-- ===========================
('Salt', 0, 0.0, 0.0, 0.0),
('Black Pepper', 251, 10.4, 64.0, 3.3),
('Paprika Powder', 282, 14.1, 54.0, 12.9),
('Soy Sauce', 53, 8.1, 4.9, 0.6),
('Tomato Paste', 82, 4.3, 18.9, 0.5),
('Honey', 304, 0.3, 82.4, 0.0),
('Bread', 265, 9.0, 49.0, 3.3);

-- ===========================
-- SAMPLE DATA - CATEGORIES
-- ===========================
-- Note: This data has been AI-generated for testing purposes

INSERT INTO categories (name, description, icon, color, display_order) VALUES
-- Meal Type (Order 1-4)
('Breakfast', 'Meals suitable for breakfast', '☀️', '#FFB347', 1),
('Lunch', 'Midday meals, light but nutritious', '🌞', '#87CEEB', 2),
('Dinner', 'Full warm meals for the evening', '🌙', '#9B59B6', 3),
('Snack', 'Small meals or snacks', '🍎', '#FF69B4', 4),

-- Diet Type (Order 5-8)
('Vegetarian', 'Meals without meat or fish', '🥬', '#28A745', 5),
('Vegan', 'Fully plant-based meals', '🌱', '#20C997', 6),
('Fish', 'Meals with fish or seafood', '🐟', '#17A2B8', 7),
('Meat', 'Meals with meat', '🥩', '#DC3545', 8),

-- Macro Focus (Order 9-11)
('High Protein', 'Meals with high protein content', '💪', '#FD7E14', 9),
('Low Carb', 'Meals with few carbohydrates', '🥗', '#6C757D', 10),
('High Carb', 'Meals with many carbohydrates', '🍝', '#FFC107', 11),

-- General Categories (Order 12-15)
('Healthy', 'Balanced meals for daily use', '💚', '#28A745', 12),
('Comfort Food', 'Rich and filling meals', '🍲', '#6F4E37', 13),
('Quick Meal', 'Meals that are quick to prepare', '⚡', '#007BFF', 14),
('Meal Prep', 'Meals suitable for advance preparation', '📦', '#6610F2', 15);

-- ===========================
-- SAMPLE DATA - TAGS
-- ===========================
-- Note: This data has been AI-generated for testing purposes

INSERT INTO tags (name, description) VALUES
-- ===========================
-- DIET & LIFESTYLE
-- ===========================
('Vegetarian', 'Contains no meat or fish'),
('Vegan', 'Fully plant-based'),
('Pescatarian', 'Contains fish but no meat'),
('Gluten-free', 'Contains no gluten'),
('Dairy-free', 'Contains no dairy'),
('Nut-free', 'Contains no nuts'),
('Sugar-free', 'Without added sugars'),
('Low Carb', 'Low in carbohydrates'),
('Keto', 'Very low carb, high fat'),
('High Protein', 'High protein content'),
('Low Calorie', 'Low in calories'),
('Healthy', 'Balanced nutritional value'),

-- ===========================
-- MEAL TYPE
-- ===========================
('Breakfast', 'Suitable as breakfast'),
('Lunch', 'Suitable as lunch'),
('Dinner', 'Suitable as dinner'),
('Snack', 'Suitable as a snack'),
('Meal Prep', 'Suitable for advance preparation'),
('Quick Meal', 'Ready within 30 minutes'),

-- ===========================
-- CUISINES / STYLES
-- ===========================
('Italian', 'Italian cuisine'),
('Asian', 'Asian cuisine'),
('Mexican', 'Mexican cuisine'),
('Mediterranean', 'Mediterranean cuisine'),
('Oriental', 'Oriental flavors'),
('Dutch', 'Traditional Dutch dishes'),
('American', 'American cuisine'),
('Indian', 'Indian cuisine'),

-- ===========================
-- FLAVOR PROFILE
-- ===========================
('Spicy', 'Spicy flavor'),
('Sweet', 'Sweet dishes'),
('Savory', 'Savory flavor'),
('Sour', 'Fresh sour taste'),
('Umami', 'Rich umami flavor'),

-- ===========================
-- COOKING & CHARACTERISTICS
-- ===========================
('Oven', 'Prepared in the oven'),
('Pan', 'Prepared in a pan'),
('Grill', 'Prepared on the grill'),
('Air Fryer', 'Suitable for air fryer'),
('One-Pot', 'Everything in one pot'),
('Budget-friendly', 'Budget-friendly meal'),
('Kid-friendly', 'Suitable for children'),
('Festive', 'Suitable for special occasions'),

-- ===========================
-- SEASONS & MOMENTS
-- ===========================
('Summer', 'Light meals for summer'),
('Winter', 'Warm meals for winter'),
('Autumn', 'Seasonal autumn dishes'),
('Spring', 'Fresh spring dishes'),
('Holidays', 'Suitable for holidays'),

-- ===========================
-- GOALS
-- ===========================
('Weight Loss', 'Suitable for weight loss'),
('Muscle Building', 'Suitable for muscle building'),
('Energy Boost', 'Provides sustained energy');

-- ===========================
-- SAMPLE DATA - RECIPES
-- ===========================
-- Note: This data has been AI-generated for testing purposes
-- Category IDs: Breakfast=1, Lunch=2, Dinner=3, Snack=4

INSERT INTO recipes (title, description, instructions, image_url, prep_time, cook_time, servings, difficulty, category_id) VALUES
-- ===========================
-- BREAKFAST RECIPES
-- ===========================
('Oatmeal with Banana and Honey',
'Creamy oatmeal with banana and honey',
'Bring the milk to a boil in a saucepan. Add the oatmeal and let it simmer gently for 5 to 7 minutes while stirring until a creamy porridge forms. Meanwhile, slice the banana. Remove the pan from the heat and stir the honey into the oatmeal. Serve with banana slices on top.',
'oatmeal_banana.jpg', 5, 10, 1, 'Easy', 1),

('Greek Yogurt with Strawberries',
'Fresh yogurt with fresh fruit',
'Wash the strawberries and cut them into pieces. Scoop the Greek yogurt into a bowl and add the strawberries. Mix gently or serve layered. Optionally you can add honey or nuts.',
'yogurt_strawberry.jpg', 5, 0, 1, 'Easy', 1),

('Omelet with Spinach',
'Fluffy omelet with fresh spinach',
'Break the eggs into a bowl and whisk them with a pinch of salt and pepper. Heat a pan with a little oil or butter. Fry the spinach briefly until wilted. Pour the egg mixture in and cook the omelet on low heat until done.',
'omelet_spinach.jpg', 5, 8, 1, 'Easy', 1),

('Avocado Toast',
'Whole grain toast with avocado',
'Toast the bread until golden brown. Halve the avocado, remove the pit and mash the flesh with a fork. Season with salt and pepper. Spread the avocado over the toast and serve immediately.',
'avocado_toast.jpg', 5, 5, 1, 'Easy', 1),

('Smoothie Bowl',
'Thick smoothie with fruit',
'Put the fruit together with a splash of milk or yogurt into a blender. Blend until a thick smoothie forms. Pour the smoothie into a bowl and garnish with fresh fruit or seeds if desired.',
'smoothie_bowl.jpg', 10, 0, 1, 'Easy', 1),

-- ===========================
-- LUNCH RECIPES
-- ===========================
('Chicken Salad',
'Fresh salad with grilled chicken',
'Season the chicken breast with salt and pepper and grill it in a pan until cooked. Let it rest briefly and slice into pieces. Chop the vegetables of your choice and mix them in a bowl. Add the chicken and mix everything with a dressing of your choice.',
'chicken_salad.jpg', 10, 10, 2, 'Easy', 2),

('Pasta Pesto',
'Quick pasta with pesto',
'Bring a pot of water to a boil and cook the pasta according to package instructions. Drain and save a small cup of cooking water. Mix the pasta with the pesto and add some cooking water if desired for extra creaminess.',
'pasta_pesto.jpg', 5, 12, 2, 'Easy', 2),

('Wrap with Hummus and Vegetables',
'Vegetarian wrap',
'Warm the wrap briefly in a pan. Spread the wrap with hummus. Cut the vegetables into thin strips and distribute over the wrap. Roll tightly and cut in half if desired.',
'wrap_hummus.jpg', 10, 0, 1, 'Easy', 2),

('Tomato Soup',
'Classic tomato soup',
'Chop the onion and fry it in a pan. Add the tomatoes and let simmer gently for 15 minutes. Puree the soup with an immersion blender and season with salt and pepper.',
'tomato_soup.jpg', 10, 20, 2, 'Medium', 2),

-- ===========================
-- DINNER RECIPES
-- ===========================
('Spaghetti Bolognese',
'Classic Italian pasta',
'Cook the spaghetti according to package instructions. Chop the onion and fry it with the ground beef until crumbled. Add tomato sauce and simmer for 20 minutes. Season with herbs. Serve the sauce over the spaghetti.',
'spaghetti_bolognese.jpg', 10, 30, 4, 'Medium', 3),

('Salmon with Rice and Broccoli',
'Healthy meal with fish',
'Cook the rice according to package instructions. Steam or cook the broccoli until tender-crisp. Fry the salmon in a pan with some oil until golden brown and cooked through. Serve everything together.',
'salmon_rice.jpg', 10, 20, 2, 'Medium', 3),

('Vegetarian Curry',
'Spicy curry with vegetables',
'Chop the vegetables. Fry them in a pan with curry paste. Add coconut milk and simmer gently for 20 minutes. Serve with rice.',
'veg_curry.jpg', 15, 25, 3, 'Medium', 3),

('Chicken Stir-fry',
'Quick Asian stir-fry',
'Cut the chicken into pieces and fry it in a hot pan. Add vegetables and stir-fry briefly. Add soy sauce and serve with rice or noodles.',
'chicken_stirfry.jpg', 10, 15, 2, 'Easy', 3),

('Lasagne',
'Oven dish with pasta and sauce',
'Preheat the oven to 180°C. Brown the ground beef and add tomato sauce. Layer sauce, lasagne noodles and cheese if desired. Bake for 40 minutes in the oven.',
'lasagne.jpg', 20, 40, 4, 'Hard', 3),

-- ===========================
-- SNACK RECIPES
-- ===========================
('Fruit Mix',
'Healthy fruit snack',
'Wash and chop different types of fruit. Mix everything in a bowl and serve immediately.',
'fruit_mix.jpg', 5, 0, 1, 'Easy', 4),

('Boiled Egg',
'Protein-rich snack',
'Bring water to a boil. Add the egg and cook for 7 minutes. Shock in cold water and peel the egg.',
'boiled_egg.jpg', 2, 7, 1, 'Easy', 4),

('Hummus with Carrot',
'Creamy hummus with raw vegetables',
'Peel the carrots and cut into strips. Serve together with hummus.',
'hummus_carrot.jpg', 5, 0, 2, 'Easy', 4);

-- ===========================
-- SAMPLE DATA - RECIPE INGREDIENTS
-- ===========================
-- Mapping: Ingredient IDs adjusted to match our 51-ingredient database

INSERT INTO recipe_ingredients (recipe_id, ingredient_id, quantity, unit) VALUES

-- ===========================
-- BREAKFAST RECIPES
-- ===========================
(1, 24, 50, 'grams'),    -- Oatmeal
(1, 36, 200, 'ml'),      -- Milk
(1, 14, 1, 'piece'),     -- Banana
(1, 50, 15, 'grams'),    -- Honey

(2, 37, 200, 'grams'),   -- Full-fat Yogurt
(2, 17, 100, 'grams'),   -- Strawberries

(3, 40, 2, 'pieces'),    -- Egg
(3, 9, 50, 'grams'),     -- Spinach
(3, 41, 5, 'grams'),     -- Butter

(4, 16, 1, 'piece'),     -- Avocado
(4, 51, 2, 'slices'),    -- Bread

(5, 14, 1, 'piece'),     -- Banana
(5, 17, 100, 'grams'),   -- Strawberries
(5, 36, 100, 'ml'),      -- Milk

-- ===========================
-- LUNCH RECIPES
-- ===========================
(6, 25, 200, 'grams'),   -- Chicken Breast
(6, 4, 100, 'grams'),    -- Tomato
(6, 5, 50, 'grams'),     -- Cucumber
(6, 42, 10, 'ml'),       -- Olive Oil

(7, 20, 200, 'grams'),   -- Pasta
(7, 42, 15, 'ml'),       -- Olive Oil
(7, 4, 100, 'grams'),    -- Tomato

(8, 23, 1, 'piece'),     -- Couscous (as wrap substitute)
(8, 33, 100, 'grams'),   -- Chickpeas (for hummus)
(8, 5, 50, 'grams'),     -- Cucumber
(8, 3, 50, 'grams'),     -- Bell Pepper

(9, 4, 400, 'grams'),    -- Tomato
(9, 1, 1, 'piece'),      -- Onion
(9, 2, 2, 'cloves'),     -- Garlic

-- ===========================
-- DINNER RECIPES
-- ===========================
(10, 20, 400, 'grams'),  -- Pasta
(10, 26, 300, 'grams'),  -- Ground Beef
(10, 1, 1, 'piece'),     -- Onion
(10, 4, 400, 'grams'),   -- Tomato

(11, 28, 200, 'grams'),  -- Salmon
(11, 18, 200, 'grams'),  -- White Rice
(11, 7, 150, 'grams'),   -- Broccoli

(12, 31, 200, 'grams'),  -- Tofu
(12, 7, 150, 'grams'),   -- Broccoli
(12, 36, 200, 'ml'),     -- Milk (coconut milk substitute)

(13, 25, 200, 'grams'),  -- Chicken Breast
(13, 3, 100, 'grams'),   -- Bell Pepper
(13, 7, 100, 'grams'),   -- Broccoli
(13, 48, 20, 'ml'),      -- Soy Sauce

(14, 20, 300, 'grams'),  -- Pasta (lasagne noodles)
(14, 26, 300, 'grams'),  -- Ground Beef
(14, 4, 400, 'grams'),   -- Tomato
(14, 39, 100, 'grams'),  -- Cheese

-- ===========================
-- SNACK RECIPES
-- ===========================
(15, 14, 1, 'piece'),    -- Banana
(15, 13, 1, 'piece'),    -- Apple
(15, 17, 100, 'grams'),  -- Strawberries

(16, 40, 1, 'piece'),    -- Egg

(17, 6, 150, 'grams'),   -- Carrot
(17, 33, 100, 'grams');  -- Chickpeas

-- ===========================
-- SAMPLE DATA - RECIPE TAGS
-- ===========================

INSERT INTO recipe_tags (recipe_id, tag_id) VALUES

-- ===========================
-- BREAKFAST RECIPES
-- ===========================
(1, 13), (1, 12), (1, 28), (1, 47),            -- Oatmeal: Breakfast, Healthy, Sweet, Energy Boost
(2, 13), (2, 12), (2, 11),                     -- Yogurt: Breakfast, Healthy, Low Calorie
(3, 13), (3, 10), (3, 37),                     -- Omelet: Breakfast, High Protein, Budget-friendly
(4, 13), (4, 12), (4, 38),                     -- Avocado Toast: Breakfast, Healthy, Kid-friendly
(5, 13), (5, 12), (5, 28), (5, 47),            -- Smoothie Bowl: Breakfast, Healthy, Sweet, Energy Boost

-- ===========================
-- LUNCH RECIPES
-- ===========================
(6, 14), (6, 10), (6, 12),                     -- Chicken Salad: Lunch, High Protein, Healthy
(7, 14), (7, 19), (7, 37),                     -- Pasta Pesto: Lunch, Italian, Budget-friendly
(8, 14), (8, 1), (8, 12),                      -- Wrap Hummus: Lunch, Vegetarian, Healthy
(9, 14), (9, 1), (9, 18),                      -- Tomato Soup: Lunch, Vegetarian, Quick Meal

-- ===========================
-- DINNER RECIPES
-- ===========================
(10, 15), (10, 19), (10, 29),                  -- Spaghetti Bolognese: Dinner, Italian, Savory
(11, 15), (11, 3), (11, 12),                   -- Salmon: Dinner, Pescatarian, Healthy
(12, 15), (12, 1), (12, 26), (12, 27),         -- Vegetarian Curry: Dinner, Vegetarian, Asian, Spicy
(13, 15), (13, 20), (13, 18),                  -- Chicken Stir-fry: Dinner, Asian, Quick Meal
(14, 15), (14, 38), (14, 39),                  -- Lasagne: Dinner, Kid-friendly, Festive

-- ===========================
-- SNACK RECIPES
-- ===========================
(15, 16), (15, 12), (15, 47),                  -- Fruit Mix: Snack, Healthy, Energy Boost
(15, 11), (16, 10), (16, 8),                   -- Boiled Egg: Snack, High Protein, Keto
(17, 16), (17, 1), (17, 37);                   -- Hummus with Carrot: Snack, Vegetarian, Budget-friendly

-- ===========================
-- SAMPLE DATA - RECIPE CATEGORIES
-- ===========================
-- Category IDs: Breakfast=1, Lunch=2, Dinner=3, Snack=4, Vegetarian=5, Vegan=6, 
--               Fish=7, Meat=8, High Protein=9, Low Carb=10, High Carb=11,
--               Healthy=12, Comfort Food=13, Quick Meal=14, Meal Prep=15

INSERT INTO recipe_categories (recipe_id, category_id) VALUES

-- ===========================
-- BREAKFAST RECIPES
-- ===========================
(1, 1), (1, 5), (1, 12), (1, 14),              -- Oatmeal: Breakfast, Vegetarian, Healthy, Quick
(2, 1), (2, 5), (2, 9), (2, 12), (2, 14),      -- Yogurt: Breakfast, Vegetarian, High Protein, Healthy, Quick
(3, 1), (3, 5), (3, 9), (3, 14),               -- Omelet: Breakfast, Vegetarian, High Protein, Quick
(4, 1), (4, 6), (4, 12), (4, 14),              -- Avocado Toast: Breakfast, Vegan, Healthy, Quick
(5, 1), (5, 6), (5, 12), (5, 14),              -- Smoothie Bowl: Breakfast, Vegan, Healthy, Quick

-- ===========================
-- LUNCH RECIPES
-- ===========================
(6, 2), (6, 8), (6, 9), (6, 10), (6, 12),      -- Chicken Salad: Lunch, Meat, High Protein, Low Carb, Healthy
(7, 2), (7, 5), (7, 11), (7, 14),              -- Pasta Pesto: Lunch, Vegetarian, High Carb, Quick
(8, 2), (8, 6), (8, 12), (8, 14),              -- Wrap Hummus: Lunch, Vegan, Healthy, Quick
(9, 2), (9, 5), (9, 12), (9, 15),              -- Tomato Soup: Lunch, Vegetarian, Healthy, Meal Prep

-- ===========================
-- DINNER RECIPES
-- ===========================
(10, 3), (10, 8), (10, 11), (10, 13),          -- Spaghetti Bolognese: Dinner, Meat, High Carb, Comfort
(11, 3), (11, 7), (11, 9), (11, 12),           -- Salmon: Dinner, Fish, High Protein, Healthy
(12, 3), (12, 5), (12, 12), (12, 15),          -- Vegetarian Curry: Dinner, Vegetarian, Healthy, Meal Prep
(13, 3), (13, 8), (13, 9), (13, 14),           -- Chicken Stir-fry: Dinner, Meat, High Protein, Quick
(14, 3), (14, 8), (14, 13), (14, 15),          -- Lasagne: Dinner, Meat, Comfort, Meal Prep

-- ===========================
-- SNACK RECIPES
-- ===========================
(15, 4), (15, 6), (15, 12), (15, 14),          -- Fruit Mix: Snack, Vegan, Healthy, Quick
(16, 4), (16, 5), (16, 9), (16, 10), (16, 14),-- Boiled Egg: Snack, Vegetarian, High Protein, Low Carb, Quick
(17, 4), (17, 6), (17, 12), (17, 14);          -- Hummus Carrot: Snack, Vegan, Healthy, Quick

