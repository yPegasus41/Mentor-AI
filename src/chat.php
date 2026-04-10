<?php 
    require __DIR__ . '/../vendor/autoload.php';
    
    //configurar o cabeçalho para responder JSON
    header('Content-Type: application/json');

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');

    try {
        $dotenv->load();
    } catch (Exception $e) {
        die(json_encode(['error' => 'Arquivo .env não encontrado!']));
    }


    //Configurar API
    $apiKey = $_ENV['HF_API_KEY'];
    $modeUrl = "https//api-inference.huggingface.co/models/mistralai/Mistral-7B-instruct-v0.3";
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);
    $userMessage = $data["message"] ?? "";


    if(empty($userMessage)){
        echo json_encode(['error'=> 'Mensagem vazia ou inválida.']);
        exit;
    }

    //Histórico
    $memoryPath = __DIR__ . '/../memory/chat_history.json';
    if (!is_dir(__DIR__ . '/../memory')) {
        mkdir(__DIR__ . '/../memory', 0777, true);
    }

    $history = [];
    if (file_exists($memoryPath)){
        $history = json_decode(file_get_contents($memoryPath), true) ?? [];
    }
    // adicionar nova mensagem do usuário ao histórico
    $history[] = ['role' => 'user', 'content' => $userMessage];
//Chamada pra IA
    try {
        $systemText = "Você um mentor de Desenvolvimento Web especialista. Ajude o alunocom PHP, CSS, HTML";

        $fullPrompt =  "<s>[INST] $systemText \n Histórico: " . json_encode($history) . "[/INST]";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $modelUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $apiKey",
            "Content-Type: application/json"
        ]);
        
        $payload = [
            'inputs' => $fullPrompt,
            'Parameters' => [
                'max_new_tokens' => 500,
                'return_full_text' => false
            ]
        ]
    }
?>