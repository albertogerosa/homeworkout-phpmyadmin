CREATE DATABASE IF NOT EXISTS home_workout;
USE home_workout;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    age INT,
    fitness_level ENUM('principiante', 'intermedio', 'avanzato') DEFAULT 'principiante',
    goal VARCHAR(100),
    notification_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    plan_completed_count INT DEFAULT 0
);

CREATE TABLE workout_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    difficulty ENUM('facile', 'medio', 'difficile') DEFAULT 'facile',
    duration_days INT DEFAULT 28,
    start_date DATE,
    end_date DATE,
    status ENUM('attivo', 'completato', 'riposo') DEFAULT 'attivo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    exercise_name VARCHAR(100),
    description TEXT,
    reps INT,
    sets INT,
    day INT,
    difficulty_increase FLOAT DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id)
);

CREATE TABLE user_progress (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    exercise_id INT NOT NULL,
    date DATE,
    completed INT DEFAULT 0,
    reps_done INT,
    sets_done INT,
    feedback TEXT,
    difficulty_level FLOAT DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);

CREATE TABLE exercise_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    exercise_name VARCHAR(100),
    total_reps INT DEFAULT 0,
    total_sets INT DEFAULT 0,
    times_completed INT DEFAULT 0,
    avg_difficulty FLOAT DEFAULT 1.0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE friendships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'blocked') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (friend_id) REFERENCES users(id)
);

CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    message VARCHAR(255),
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE rest_periods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    consecutive_days INT DEFAULT 0,
    rest_days_needed INT DEFAULT 1,
    last_workout DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE plan_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    rating INT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (plan_id) REFERENCES workout_plans(id)
);
