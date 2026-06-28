<?php

class ServiceModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getServicesByProviderId($provider_id) {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE provider_id = ? ORDER BY name");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getServiceById($id) {
        $stmt = $this->db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addService($data) {
        $stmt = $this->db->prepare("
            INSERT INTO services (provider_id, name, description, cost, availability)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['provider_id'],
            $data['name'],
            $data['description'],
            $data['cost'],
            $data['availability']
        ]);
    }

    public function updateService($data) {
        $stmt = $this->db->prepare("
            UPDATE services 
            SET name = ?, description = ?, cost = ?, availability = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['cost'],
            $data['availability'],
            $data['id']
        ]);
    }

    public function getProviderDetails($user_id) {
        $stmt = $this->db->prepare("
            SELECT sp.*, u.email 
            FROM service_providers sp
            INNER JOIN users u ON sp.user_id = u.id
            WHERE sp.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProviderProfile($data) {
        $stmt = $this->db->prepare("
            UPDATE service_providers 
            SET name = ?, phone = ?, service_area = ?, vehicle_info = ?, 
                availability_hours = ?, service_description = ?
            WHERE user_id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['service_area'],
            $data['vehicle_info'],
            $data['availability_hours'],
            $data['service_description'],
            $data['user_id']
        ]);
    }

    public function getBookingStats($provider_id) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_bookings,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_bookings
            FROM cab_bookings 
            WHERE provider_id = ?
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBookingHistory($provider_id) {
        $stmt = $this->db->prepare("
            SELECT cb.*, p.name as patient_name, p.phone as patient_phone
            FROM cab_bookings cb
            INNER JOIN patients p ON cb.patient_id = p.id 
            WHERE cb.provider_id = ? 
            ORDER BY cb.created_at DESC
        ");
        $stmt->execute([$provider_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
