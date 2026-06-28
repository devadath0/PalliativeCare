<?php
/**
 * Symptom Search View
 * This file displays the symptom search interface for patients
 */

// Set page title
$page_title = 'Symptom Search';

// Include header
require_once __DIR__ . '/../../../views/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Symptom Search</h4>
                </div>
                <div class="card-body">
                    <p class="lead">Enter your symptoms to find possible conditions and recommended doctors.</p>
                    
                    <form id="symptomSearchForm" method="post" action="/palliative/modules/patient/process_symptom_search.php">
                        <div class="form-group mb-3">
                            <label for="symptomInput">Enter a symptom:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="symptomInput" placeholder="Type a symptom and press Enter" list="commonSymptomsList">
                                <datalist id="commonSymptomsList">
                                    <option value="Fever">
                                    <option value="Headache">
                                    <option value="Cough">
                                    <option value="Fatigue">
                                    <option value="Nausea">
                                    <option value="Sore throat">
                                    <option value="Shortness of breath">
                                    <option value="Chest pain">
                                    <option value="Dizziness">
                                    <option value="Rash">
                                </datalist>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="addSymptomBtn">Add</button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Press Enter after typing each symptom or select from the suggestions</small>
                        </div>
                        
                        <div class="selected-symptoms mb-3">
                            <label for="selectedSymptoms">Selected Symptoms:</label>
                            <div id="selectedSymptoms" class="d-flex flex-wrap">
                                <!-- Selected symptoms will appear here -->
                                <div class="empty-symptoms-message text-muted">No symptoms selected yet</div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" id="searchBtn" disabled>Search</button>
                    </form>
                    
                    <div class="mt-4" id="loadingResults" style="display: none;">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <p class="text-center mt-2">Searching for conditions and doctors...</p>
                    </div>
                    
                    <div class="mt-4" id="searchResults" style="display: none;">
                        <h5>Search Results</h5>
                        
                        <div id="errorMessage" class="alert alert-danger" style="display: none;"></div>
                        
                        <div id="diseasesResults">
                            <h6>Possible Conditions:</h6>
                            <div class="list-group" id="diseasesList">
                                <!-- Diseases will appear here -->
                            </div>
                        </div>
                        
                        <div id="doctorsResults" class="mt-4">
                            <h6>Recommended Doctors:</h6>
                            <div class="row" id="doctorsList">
                                <!-- Doctors will appear here -->
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($recent_searches)): ?>
                    <div class="mt-4">
                        <h5>Your Recent Searches</h5>
                        <div class="list-group">
                            <?php foreach ($recent_searches as $search): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Symptoms: <?php echo htmlspecialchars($search['symptoms']); ?></h6>
                                    <small><?php echo date('M d, Y H:i', strtotime($search['created_at'])); ?></small>
                                </div>
                                <?php if (!empty($search['diseases_found'])): ?>
                                <p class="mb-1"><small>Conditions: <?php echo htmlspecialchars($search['diseases_found']); ?></small></p>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.symptom-tag {
    display: inline-block;
    background-color: #e9ecef;
    border-radius: 20px;
    padding: 8px 15px;
    margin: 5px;
    font-size: 0.9rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.symptom-tag button {
    background: none;
    border: none;
    color: #dc3545;
    margin-left: 8px;
    cursor: pointer;
    padding: 0;
    font-size: 1.1rem;
}

.symptom-tag button:hover {
    color: #c82333;
}

.results-section {
    margin-top: 2rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.disease-card, .doctor-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    background-color: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}

.disease-card:hover, .doctor-card:hover {
    transform: translateY(-2px);
}

.disease-card h4, .doctor-card h4 {
    color: #0d6efd;
    margin-bottom: 1rem;
}

.error-message {
    color: #dc3545;
    padding: 1rem;
    border: 1px solid #dc3545;
    border-radius: 4px;
    margin-top: 1rem;
    background-color: #fff;
}

.book-appointment {
    background-color: #198754;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 0.5rem;
    transition: background-color 0.2s;
}

.book-appointment:hover {
    background-color: #157347;
    color: white;
}

.severity-high {
    color: #dc3545;
}

.severity-medium {
    color: #ffc107;
}

.severity-low {
    color: #198754;
}
</style>

<script src="/palliative/js/symptom-search.js"></script>

<?php 
// Include footer
require_once __DIR__ . '/../../../views/includes/footer.php'; 
?> 