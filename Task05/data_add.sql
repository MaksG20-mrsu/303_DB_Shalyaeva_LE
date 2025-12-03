INSERT INTO users (name, email, gender, register_date, occupation) 
VALUES ('Шаляева Любовь', 'vv1nc3ntt@gmail.com', 'female', datetime('now', 'localtime'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation) 
VALUES ('Ямашкина Елизавета', 'ryzhkinvlad@gmail.com', 'male', datetime('now', 'localtime'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation) 
VALUES ('Чесноков Андрей', 'rybakovevgeniy@gmail.com', 'male', datetime('now', 'localtime'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation) 
VALUES ('Ферафонтов Алексей', 'tomilinilya@gmail.com', 'male', datetime('now', 'localtime'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation) 
VALUES ('Сковородникова Алёна', 'tulskovilya@gmail.com', 'male', datetime('now', 'localtime'), 'student');

INSERT INTO movies (title, year)
VALUES ('Now You See Me: Now You Don’t', 2025);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Now You See Me: Now You Don’t' AND year = 2025),
    (SELECT id FROM genres WHERE name = 'Thriller')
);

INSERT INTO movies (title, year)
VALUES ('Cheburashka', 2023);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Cheburashka' AND year = 2023),
    (SELECT id FROM genres WHERE name = 'Comedy')
);

INSERT INTO movies (title, year)
VALUES ('Alice's Adventures in Wonderland', 2025);

INSERT INTO movie_genres (movie_id, genre_id)
VALUES (
    (SELECT id FROM movies WHERE title = 'Alice's Adventures in Wonderland' AND year = 2025),
    (SELECT id FROM genres WHERE name = 'Fantasy')
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'edgaradamovic84@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Now You See Me: Now You Don’t' AND year = 2025),
    5.0,
    strftime('%s', 'now')
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'edgaradamovic84@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Cheburashka' AND year = 2023),
    2.7,
    strftime('%s', 'now')
);

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
VALUES (
    (SELECT id FROM users WHERE email = 'edgaradamovic84@gmail.com'),
    (SELECT id FROM movies WHERE title = 'Alice's Adventures in Wonderland' AND year = 2025),
    3.0,
    strftime('%s', 'now')
);