<?php
require_once __DIR__ . '/../config/Database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class SESMailer {
    private $conn;
    private $config;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->loadConfig();
    }
    
    private function loadConfig() {
        $query = "SELECT * FROM ses_config WHERE is_active = 1 LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $this->config = $stmt->fetch();
    }
    
    public function sendEmail($to, $subject, $body, $isHTML = true, $templateId = null) {
        if (!$this->config) {
            throw new Exception("SES configuration not found");
        }
        
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->config['smtp_port'];
            
            // Recipients
            $mail->setFrom($this->config['from_email'], $this->config['from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            if ($isHTML) {
                $mail->AltBody = strip_tags($body);
            }
            
            $result = $mail->send();
            
            // Log the email
            $this->logEmail($to, $subject, $templateId, 'sent', $mail->getLastMessageID());
            
            return [
                'success' => true,
                'message_id' => $mail->getLastMessageID(),
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            // Log the error
            $this->logEmail($to, $subject, $templateId, 'failed', null, $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function sendBulkEmails($recipients, $subject, $body, $isHTML = true) {
        $results = [];
        foreach ($recipients as $email) {
            $results[] = [
                'email' => $email,
                'result' => $this->sendEmail($email, $subject, $body, $isHTML)
            ];
        }
        return $results;
    }
    
    private function logEmail($to, $subject, $templateId, $status, $messageId = null, $error = null) {
        $query = "INSERT INTO email_logs (user_id, test_type, recipient_email, subject, template_id, status, message_id, error_message) 
                  VALUES (:user_id, :test_type, :recipient_email, :subject, :template_id, :status, :message_id, :error_message)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindValue(':test_type', $templateId ? 'template' : 'basic');
        $stmt->bindParam(':recipient_email', $to);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':template_id', $templateId);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':message_id', $messageId);
        $stmt->bindParam(':error_message', $error);
        $stmt->execute();
    }
    
    public function updateConfig($config) {
        $query = "UPDATE ses_config SET 
                  smtp_host = :smtp_host,
                  smtp_port = :smtp_port,
                  smtp_username = :smtp_username,
                  smtp_password = :smtp_password,
                  from_email = :from_email,
                  from_name = :from_name
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':smtp_host', $config['smtp_host']);
        $stmt->bindParam(':smtp_port', $config['smtp_port']);
        $stmt->bindParam(':smtp_username', $config['smtp_username']);
        $stmt->bindParam(':smtp_password', $config['smtp_password']);
        $stmt->bindParam(':from_email', $config['from_email']);
        $stmt->bindParam(':from_name', $config['from_name']);
        $stmt->bindParam(':id', $this->config['id']);
        
        return $stmt->execute();
    }
    
    public function getConfig() {
        return $this->config;
    }
}
?>
