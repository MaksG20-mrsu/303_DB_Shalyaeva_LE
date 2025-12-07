-- Включаем поддержку внешних ключей
PRAGMA foreign_keys = ON;

CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL,
    hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
    dismissal_date DATE,
    revenue_percent REAL NOT NULL DEFAULT 30.0 CHECK (revenue_percent BETWEEN 0 AND 100),
    is_active BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    duration_minutes INTEGER NOT NULL CHECK (duration_minutes > 0),
    price REAL NOT NULL CHECK (price >= 0),
    is_available BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    client_name TEXT NOT NULL,
    client_phone TEXT NOT NULL,
    appointment_datetime DATETIME NOT NULL,
    expected_end_datetime DATETIME NOT NULL,
    status TEXT NOT NULL DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'completed', 'cancelled', 'no_show')),
    total_price REAL NOT NULL DEFAULT 0 CHECK (total_price >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT
);

CREATE INDEX idx_appointments_employee_datetime ON appointments(employee_id, appointment_datetime, expected_end_datetime);

CREATE TABLE appointment_services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1 CHECK (quantity > 0),
    price_at_time REAL NOT NULL CHECK (price_at_time >= 0),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE work_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    actual_duration_minutes INTEGER NOT NULL CHECK (actual_duration_minutes > 0),
    completed_datetime DATETIME NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE salary_calculations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    calculation_date DATE NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    total_revenue REAL NOT NULL DEFAULT 0,
    employee_percent REAL NOT NULL,
    salary_amount REAL NOT NULL DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE RESTRICT
);

-- ========== ЗАПОЛНЕНИЕ ТЕСТОВЫМИ ДАННЫМИ ==========

INSERT INTO employees (first_name, last_name, hire_date, dismissal_date, revenue_percent, is_active) VALUES
('Иван', 'Петров', '2023-01-15', NULL, 30.0, 1),
('Алексей', 'Сидоров', '2023-03-10', NULL, 35.0, 1),
('Сергей', 'Иванов', '2022-11-05', '2024-01-31', 25.0, 0),
('Мария', 'Кузнецова', '2024-02-01', NULL, 32.5, 1);

INSERT INTO services (name, duration_minutes, price, is_available) VALUES
('Замена масла', 30, 2500.00, 1),
('Замена тормозных колодок', 90, 4500.00, 1),
('Диагностика двигателя', 60, 3500.00, 1),
('Замена фильтра воздуха', 20, 1800.00, 1),
('Регулировка развал-схождения', 45, 4000.00, 1),
('Шиномонтаж (комплект)', 40, 3000.00, 1);

INSERT INTO appointments (employee_id, client_name, client_phone, appointment_datetime, expected_end_datetime, status, total_price) VALUES
(1, 'Александр Белов', '+79161234567', '2024-03-20 10:00:00', '2024-03-20 11:30:00', 'completed', 5000.00),
(2, 'Екатерина Смирнова', '+79167654321', '2024-03-20 14:00:00', '2024-03-20 14:30:00', 'scheduled', 1500.00),
(1, 'Дмитрий Козлов', '+79162345678', '2024-03-21 09:00:00', '2024-03-21 10:30:00', 'scheduled', 4300.00),
(4, 'Ольга Новикова', '+79168765432', '2024-03-19 11:00:00', '2024-03-19 12:00:00', 'completed', 2500.00);

INSERT INTO appointment_services (appointment_id, service_id, quantity, price_at_time) VALUES
(1, 3, 1, 3500.00), -- Диагностика двигателя
(1, 1, 1, 2500.00), -- Замена масла
(1, 4, 1, 1800.00),  -- Замена фильтра воздуха
(2, 1, 1, 2500.00), -- Замена масла
(3, 2, 1, 4500.00), -- Замена тормозных колодок
(3, 4, 1, 1800.00),  -- Замена фильтра воздуха
(4, 3, 1, 3500.00); -- Диагностика двигателя

INSERT INTO work_records (appointment_id, employee_id, service_id, actual_duration_minutes, completed_datetime, notes) VALUES
(1, 1, 3, 55, '2024-03-20 10:55:00', 'Диагностика показала норму'),
(1, 1, 1, 35, '2024-03-20 11:30:00', 'Залито синтетическое масло'),
(1, 1, 4, 25, '2024-03-20 11:55:00', 'Фильтр заменен'),
(4, 4, 3, 60, '2024-03-19 12:00:00', 'Неполадок не обнаружено');

INSERT INTO salary_calculations (employee_id, calculation_date, period_start, period_end, total_revenue, employee_percent, salary_amount) VALUES
(1, '2024-03-01', '2024-03-01', '2024-03-31', 5000.00, 30.0, 1500.00),
(4, '2024-03-01', '2024-03-01', '2024-03-31', 2500.00, 32.5, 812.50);

-- ========== СЛУЖЕБНЫЕ ФУНКЦИИ И ВЬЮХИ ==========

CREATE VIEW view_current_appointments AS
SELECT 
    a.id,
    a.appointment_datetime,
    a.expected_end_datetime,
    a.client_name,
    a.client_phone,
    a.status,
    a.total_price,
    e.first_name || ' ' || e.last_name as employee_name,
    e.is_active
FROM appointments a
JOIN employees e ON a.employee_id = e.id
WHERE a.appointment_datetime >= datetime('now', '-1 day')
ORDER BY a.appointment_datetime;

CREATE VIEW view_salary_calculation AS
SELECT 
    wr.employee_id,
    e.first_name || ' ' || e.last_name as employee_name,
    e.revenue_percent,
    SUM(s.price) as total_revenue,
    SUM(s.price) * e.revenue_percent / 100 as salary
FROM work_records wr
JOIN appointments a ON wr.appointment_id = a.id
JOIN employees e ON wr.employee_id = e.id
JOIN services s ON wr.service_id = s.id
WHERE a.status = 'completed'
GROUP BY wr.employee_id;

-- Триггер для автоматического обновления total_price в записи
CREATE TRIGGER update_appointment_total_price
AFTER INSERT ON appointment_services
BEGIN
    UPDATE appointments
    SET total_price = (
        SELECT SUM(price_at_time * quantity)
        FROM appointment_services
        WHERE appointment_id = NEW.appointment_id
    )
    WHERE id = NEW.appointment_id;
END;

CREATE TRIGGER update_employee_status
AFTER UPDATE OF dismissal_date ON employees
WHEN NEW.dismissal_date IS NOT NULL
BEGIN
    UPDATE employees
    SET is_active = 0
    WHERE id = NEW.id;
END;

SELECT 'База данных успешно создана и заполнена тестовыми данными!' as message;