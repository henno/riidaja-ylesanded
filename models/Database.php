<?php
class Database {
  private static $pdo = null;

  public static function connect() {
    if (self::$pdo === null) {
      self::$pdo = new PDO('sqlite:' . __DIR__ . '/../database.db');
      self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return self::$pdo;
  }
}
