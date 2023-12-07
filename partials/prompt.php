<?php

// Démarrer la session
session_start();

// Charger les variables d'environnement à partir du fichier .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/..");
$dotenv->safeLoad();

$api_response = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (isset($_POST["prompt"]) && !empty($_POST["prompt"])) {
    $user_prompt = $_POST["prompt"];

    $prompt = "
    [System]
    Tu es un assistant qui explique du code qui provient de n'importe quel langage de programmation.

    [User]
    Expliquer le code suivant : {$user_prompt}
    ";

    $openai_api_key = $_ENV["OPENAI_API_KEY"];
    $client = OpenAI::client($openai_api_key);

    try {
      $result = $client->chat()->create([
        "model" => "gpt-3.5-turbo",
        "messages" => [
          [
            "role" => "user",
            "content" => $prompt,
          ],
        ],
      ]);

      $api_response = $result->choices[0]->message->content;

      // Stocker la réponse de l'API dans la session
      $_SESSION["api_response"] = $api_response;

      // Rediriger vers la page actuelle pour éviter la réémission du formulaire
      header("Location: " . $_SERVER["PHP_SELF"]);
      exit();
    } catch (\Throwable $th) {
      $api_response = "Une erreur s'est produite lors de la communication avec l'API.";
    }
  } else {
    $api_response = "Veuillez saisir votre code";
  }
} else {
  $api_response = isset($_SESSION["api_response"]) ? $_SESSION["api_response"] : "";

  // Supprimer la réponse de l'API stockée de la session
  unset($_SESSION["api_response"]);
}
?>

<section class="mt-5">
  <form class="d-flex flex-column align-items-center gap-3" method="post">
    <label class="form-label h4" for="prompt">Saisissez votre code</label>
    <textarea class="form-control mb-3" id="prompt" name="prompt" type="text" rows="8" required></textarea>
    <input class="btn btn-primary" type="submit" value="Soumettre">
  </form>

  <div class="mt-5">
    <p class="text-center">
      <?= $api_response; ?>
    </p>
  </div>
</section>