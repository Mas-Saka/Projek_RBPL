CREATE TABLE verifikasi_otp (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    seminar_id INT,
    otp VARCHAR(6),
    expired_at DATETIME
);