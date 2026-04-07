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
    $apiKey = $_ENV['sk-1d870514bc95443bbfdd9abd6c6ccefa'];

    $client = OpenAI::factory()
    ->withApiKey($apiKey)
    ->withBaseUri('https://api.deepseek.com/v1')
    ->make();

    if(empty($userMessage)){
        echo json_encode(['error'=> 'Mensagem vazia ou inválida.']);
        exit;
    }

    //Histórico
    $memoryPath = __DIR__ . '/../memory/chat_history.json';

    $history = [];
    if (file_exists($memoryPath)){
        $history = json_decode(file_get_contents($memoryPath), true) ?? [];
    }
    // adicionar nova mensagem do usuário ao histórico
    $history[] = ['role' => 'user', 'content' => $userMessage];
//Chamada pra IA
    try {
        //Definir o comportamento do mentor
        $systemPrompt = [
            //Protocolos: User: a pessoa que irá interagir
            //Assistant: a IA respondendo
            //System: quem define a regra do jogo. "Mestre"
            //content: moldo a personalidade do meu mentor
            'role' => 'system', 
            'content' => 'Você é um mentor de Desenvolvimento Web especialista.'.
                        'Ajude o aluno com PHP, HTML, CSS e JS'.
                        'Sempre mostre exemplos de códigos formatados com Markdown e explique a lógica de forma simples e didática'
        ];

        //Mesclar o prompt do sistema com o histórico guardado
        $messages = array_merge([$systemPrompt], $history);

        $response = $client->chat()->create([
            'model' => 'deepseek-chat',
            'messages' => $messages,
            'temperature' => 0.7 // equilíbrio entre criatividade e precisão
        ]);

        $botReply = $response ->choices[0]->message->content;

        //Atualiza e salva o histórico
        $history[] = ['role' => 'assistant', 'content' =>$botReply];

        //ultimas 10 msgs para n estourar o limite de tokes da API
        if (count($history) >10) {
            $history = array_slice($history, -10);
        }

        file_put_contents($memoryPath, json_encode($history, JSON_PRETTY_PRINT));

        echo json_enconde(['reply' => $botReply]);

    } catch(Exception $e) {
        echo json_encode(['error' => 'Erro na API: '. $e->getMessage()]);
    }

?>