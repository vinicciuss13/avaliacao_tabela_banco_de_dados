<?php
header("content-type: application/json; charset=UTF-8");
header("access-control-allow-origin: *");
header("access-control-allow-methods: GET, POST, PUT, DELETE, OPTIONS");

//configuração do banco
$host = "localhost";
$user = "root";
$pass = "";
$db = "avaliacao_tabela_banco_de_dados";

//conexão com o banco
$con = new mysqli($host, $user, $pass, $db);

//verificação de erro
if($con->connect_error){
    http_response_code(500);
    echo json_encode(['error' => 'falha na conexão com o banco de dados: ' . $con->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
        if(isset($_GET['pesquisa'])){
            $pesquisa = '%' . $_GET['pesquisa'] . '%';
            $stmt = $con->prepare("SELECT * FROM produtos WHERE nome LIKE ?");
            $stmt->bind_param("s", $pesquisa);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $con->query("SELECT * FROM produtos ORDER BY id DESC");
        }

        $retorno = [];
        while($linha = $result->fetch_assoc()){
            // Renomeia os campos para o frontend
            $retorno[] = [
                'IDproduto' => $linha['id'],
                'NomeProduto' => $linha['nome'],
                'PrecoProduto' => $linha['preco'],
                'Disponibilidade' => $linha['disponibilidade']
            ];
        }
        echo json_encode($retorno);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $con->prepare("INSERT INTO produtos (nome, preco, disponibilidade) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $data['NomeProduto'], $data['PrecoProduto'], $data['Disponibilidade']);
        $stmt->execute();
        echo json_encode(['status' => 'sucesso', 'id' => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $con->prepare("UPDATE produtos SET nome = ?, preco = ?, disponibilidade = ? WHERE id = ?");
        $stmt->bind_param("sdii", $data['NomeProduto'], $data['PrecoProduto'], $data['Disponibilidade'], $data['IDproduto']);
        $stmt->execute();
        echo json_encode(['status' => 'sucesso']);
        break;

    case 'DELETE':
        $id = isset($_GET['IDproduto']) ? $_GET['IDproduto'] : 0;
        $stmt = $con->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['status' => 'sucesso']);
        break;
}
$con->close();
?>