<?php

$host = "localhost";
$porta = "3306";
$banco = "atendelab";
$usuario = "root";
$senha = "";
    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8",
            $usuario,
            $senha
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Conexão realizada com sucesso!";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }