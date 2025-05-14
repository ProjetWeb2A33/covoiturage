<?php
class MockGeminiController {
    /**
     * Process a chat message and return a mock response
     */
    public function processMessage($message) {
        // Create a mock response based on the message
        $response = $this->generateMockResponse($message);
        return json_encode(['result' => $response]);
    }
    
    /**
     * Generate a mock response based on the message
     */
    private function generateMockResponse($message) {
        $message = strtolower($message);
        
        // Check for common greetings
        if (preg_match('/(hello|hi|hey|bonjour|salut)/i', $message)) {
            return "Bonjour ! Je suis l'assistant virtuel d'EasyParki. Comment puis-je vous aider avec vos projets de voyage aujourd'hui ?";
        }
        
        // Check for hotel booking questions
        if (preg_match('/(réserv|hotel|chambre|logement)/i', $message)) {
            return "Pour réserver un hôtel, vous pouvez utiliser notre formulaire de réservation en ligne. Sélectionnez votre destination, les dates de séjour et le nombre de personnes. Vous pourrez ensuite choisir parmi les hôtels disponibles et finaliser votre réservation.";
        }
        
        // Check for pricing questions
        if (preg_match('/(prix|tarif|coût|combien)/i', $message)) {
            return "Nos tarifs varient selon la saison, le type de chambre et la destination. Vous pouvez consulter les prix exacts en effectuant une recherche sur notre site avec vos dates de voyage spécifiques.";
        }
        
        // Check for cancellation questions
        if (preg_match('/(annul|rembours)/i', $message)) {
            return "Notre politique d'annulation permet généralement un remboursement complet si vous annulez au moins 48 heures avant la date d'arrivée. Pour plus de détails, veuillez consulter les conditions spécifiques de votre réservation.";
        }
        
        // Check for contact questions
        if (preg_match('/(contact|téléphone|email|appeler)/i', $message)) {
            return "Vous pouvez contacter notre service client par téléphone au +216 50 084 004 ou par email à contact@easyparki.com. Notre équipe est disponible 7j/7 de 8h à 22h.";
        }
        
        // Check for thank you messages
        if (preg_match('/(merci|thanks)/i', $message)) {
            return "Je vous en prie ! N'hésitez pas si vous avez d'autres questions. Je suis là pour vous aider à planifier votre séjour parfait.";
        }
        
        // Check for goodbye messages
        if (preg_match('/(au revoir|bye|adieu)/i', $message)) {
            return "Au revoir ! Merci d'avoir utilisé l'assistant EasyParki. Bon voyage et à bientôt !";
        }
        
        // Default response for other messages
        return "Je suis l'assistant virtuel d'EasyParki, spécialisé dans les réservations d'hôtels et la planification de voyages. Je peux vous aider avec vos questions sur les réservations, les tarifs, ou les destinations. N'hésitez pas à me demander des informations spécifiques sur nos services.";
    }
}
?>
