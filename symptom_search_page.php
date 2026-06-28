<?php
require_once 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Symptom Search & Doctor Recommendation</h3>
                </div>
                <div class="card-body">
                    <form id="symptom-search-form" class="mb-4">
                        <div class="form-group">
                            <label for="symptom-input">Enter Your Symptoms</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="symptom-input" 
                                       placeholder="Type a symptom and press Enter (e.g., fever, headache, cough)" list="common-symptoms">
                                <datalist id="common-symptoms">
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
                                    <option value="Abdominal pain">
                                    <option value="Vomiting">
                                    <option value="Diarrhea">
                                    <option value="Muscle pain">
                                    <option value="Joint pain">
                                    <option value="Back pain">
                                    <option value="Runny nose">
                                    <option value="Congestion">
                                    <option value="Sneezing">
                                    <option value="Itchy eyes">
                                </datalist>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary" onclick="addSymptom()">
                                        Add Symptom
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Press Enter or click Add Symptom to add each symptom</small>
                        </div>

                        <div id="selected-symptoms" class="mb-3">
                            <!-- Selected symptoms will appear here as tags -->
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search Symptoms
                        </button>
                    </form>

                    <div id="loading-spinner" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>

                    <div id="search-results">
                        <!-- Search results will be displayed here -->
                    </div>
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

<script src="js/symptom-search.js"></script>

<?php
require_once 'includes/footer.php';
?> 