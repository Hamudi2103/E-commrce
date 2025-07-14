<?php
session_start();


if (!isset($_SESSION['usuario_id']) || (isset($_SESSION['rol']) && $_SESSION['rol'] !== 'admin')) {
    header('Location: index.php'); 
    exit();
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id) {
        try {
            $stmt = $pdo->prepare("DELETE FROM zapatos WHERE id = ?");
            $stmt->execute([$id]);
        } catch (\PDOException $e) {
            
            error_log("Error al eliminar el zapato: " . $e->getMessage());
        }
    }
}


header('Location: index.php');
exit();
?>