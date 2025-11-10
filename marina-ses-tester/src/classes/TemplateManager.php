<?php
require_once __DIR__ . '/../config/Database.php';

class TemplateManager {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getAllTemplates() {
        $query = "SELECT * FROM email_templates ORDER BY type, name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getTemplate($id) {
        $query = "SELECT * FROM email_templates WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function processTemplate($templateId, $variables = []) {
        $template = $this->getTemplate($templateId);
        if (!$template) {
            return null;
        }
        
        $subject = $template['subject'];
        $bodyHtml = $template['body_html'];
        $bodyText = $template['body_text'];
        
        // Replace variables in template
        foreach ($variables as $key => $value) {
            $placeholder = '{' . strtoupper($key) . '}';
            $subject = str_replace($placeholder, $value, $subject);
            $bodyHtml = str_replace($placeholder, $value, $bodyHtml);
            $bodyText = str_replace($placeholder, $value, $bodyText);
        }
        
        return [
            'id' => $template['id'],
            'name' => $template['name'],
            'type' => $template['type'],
            'subject' => $subject,
            'body_html' => $bodyHtml,
            'body_text' => $bodyText
        ];
    }
    
    public function getTemplateVariables($templateId) {
        $template = $this->getTemplate($templateId);
        if (!$template) {
            return [];
        }
        
        // Extract variables from template content
        $content = $template['subject'] . ' ' . $template['body_html'] . ' ' . $template['body_text'];
        preg_match_all('/\{([A-Z_]+)\}/', $content, $matches);
        
        return array_unique($matches[1]);
    }
}
?>
