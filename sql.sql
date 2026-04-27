-- Crear base de datos
CREATE DATABASE IF NOT EXISTS recetas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recetas_db;

-- Tabla usuarios
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla continentes
CREATE TABLE continents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla países
CREATE TABLE countries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  continent_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  FOREIGN KEY (continent_id) REFERENCES continents(id) ON DELETE CASCADE
);

-- Tabla recetas
CREATE TABLE recipes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country_id INT NOT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  ingredients TEXT NOT NULL,
  steps TEXT NOT NULL,
  image_url VARCHAR(255),
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
);

-- Tabla curiosidades
CREATE TABLE curiosities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country_id INT NOT NULL,
  content TEXT NOT NULL,
  FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
);

-- Tabla comentarios
CREATE TABLE comments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  recipe_id INT NOT NULL,
  content TEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  reported BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Tabla likes
CREATE TABLE likes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  comment_id INT NOT NULL,
  UNIQUE KEY unique_like (user_id, comment_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Tabla reportes
CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  comment_id INT NOT NULL,
  reason TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
);

-- Tabla última receta visitada
CREATE TABLE user_last_recipe (
  user_id INT PRIMARY KEY,
  recipe_id INT NOT NULL,
  last_visited_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

-- Insertar continentes
INSERT INTO continents (name) VALUES 
('Africa'), ('Asia'), ('Europe'), ('North America'), ('South America'), ('Oceania');

-- Insertar países
INSERT INTO countries (continent_id, name) VALUES
(1, 'Nigeria'),
(2, 'China'),
(3, 'Spain'),
(4, 'USA'),
(5, 'Brazil'),
(6, 'Australia');

-- Insertar recetas
INSERT INTO recipes (country_id, title, description, ingredients, steps, image_url) VALUES
(1, 'Jollof Rice', 'Traditional West African rice dish.', 'Rice, Tomatoes, Onion, Pepper, Spices', '1. Wash rice\n2. Prepare sauce\n3. Cook rice with sauce', 'images/jollof.jpg'),
(2, 'Kung Pao Chicken', 'Spicy stir-fried chicken with peanuts.', 'Chicken, Peanuts, Chili, Soy sauce', '1. Marinate chicken\n2. Stir-fry ingredients\n3. Serve hot', 'images/kungpao.jpg'),
(3, 'Paella', 'Famous Spanish rice dish.', 'Rice, Saffron, Seafood, Chicken, Vegetables', '1. Prepare broth\n2. Cook rice with ingredients\n3. Serve warm', 'images/paella.jpg'),
(4, 'Burger', 'Classic American burger.', 'Beef patty, Bun, Lettuce, Tomato, Cheese', '1. Grill patty\n2. Assemble burger\n3. Serve', 'images/burger.jpg'),
(5, 'Feijoada', 'Brazilian black bean stew.', 'Black beans, Pork, Sausage, Rice', '1. Cook beans\n2. Add meats\n3. Serve with rice', 'images/feijoada.jpg'),
(6, 'Meat Pie', 'Australian savory pie.', 'Pastry, Beef, Gravy, Vegetables', '1. Prepare filling\n2. Bake pie\n3. Serve hot', 'images/meatpie.jpg');

-- Insertar curiosidades 
INSERT INTO curiosities (country_id, content) VALUES
(1, 'Nigeria is the most populous country in Africa.'),
(2, 'China has the world\'s largest population.'),
(3, 'Spain is famous for its festivals and cuisine.'),
(4, 'USA is known for its cultural diversity.'),
(5, 'Brazil hosts the largest carnival in the world.'),
(6, 'Australia is home to unique wildlife.');
