-- Sample data for symptoms table
INSERT INTO `symptoms` (`name`, `description`) VALUES
('Fever', 'Elevated body temperature above the normal range'),
('Headache', 'Pain in any region of the head'),
('Cough', 'Sudden expulsion of air from the lungs to clear the air passages'),
('Fatigue', 'Extreme tiredness resulting from mental or physical exertion'),
('Sore throat', 'Pain or irritation in the throat that often worsens when swallowing'),
('Runny nose', 'Excess drainage of mucus from the nose'),
('Muscle ache', 'Pain in muscles throughout the body'),
('Shortness of breath', 'Difficulty breathing or feeling like you can\'t get enough air'),
('Chest pain', 'Discomfort or pain in the chest area'),
('Nausea', 'Feeling of sickness with an inclination to vomit'),
('Vomiting', 'Forceful expulsion of stomach contents through the mouth'),
('Diarrhea', 'Loose, watery stools occurring more frequently than usual'),
('Rash', 'Area of irritated or swollen skin'),
('Joint pain', 'Discomfort that arises from any joint'),
('Dizziness', 'Feeling faint, lightheaded, or unsteady'),
('Chills', 'Feeling of coldness accompanied by shivering'),
('Loss of appetite', 'Reduced desire to eat'),
('Abdominal pain', 'Pain that occurs between the chest and pelvic regions'),
('Swollen glands', 'Enlarged lymph nodes, usually in the neck, armpits, or groin');

-- Sample data for diseases table
INSERT INTO `diseases` (`name`, `description`, `treatment`, `severity_level`) VALUES
('Common Cold', 'A viral infectious disease of the upper respiratory tract that primarily affects the nose', 'Rest, fluids, over-the-counter medications for symptom relief', 'low'),
('Influenza', 'A contagious respiratory illness caused by influenza viruses', 'Antiviral medications, rest, fluids, pain relievers', 'medium'),
('COVID-19', 'A respiratory illness caused by the SARS-CoV-2 virus', 'Supportive care, antiviral medications, rest, isolation', 'high'),
('Strep Throat', 'A bacterial infection that can make your throat feel sore and scratchy', 'Antibiotics, pain relievers, rest, warm liquids', 'medium'),
('Bronchitis', 'Inflammation of the lining of the bronchial tubes', 'Rest, fluids, over-the-counter medications, humidifier', 'medium'),
('Pneumonia', 'Infection that inflames air sacs in one or both lungs', 'Antibiotics, rest, fluids, oxygen therapy if severe', 'high'),
('Sinusitis', 'Inflammation or swelling of the tissue lining the sinuses', 'Nasal decongestants, pain relievers, nasal irrigation', 'low'),
('Gastroenteritis', 'Inflammation of the stomach and intestines', 'Fluid replacement, rest, gradual reintroduction of food', 'medium'),
('Migraine', 'A headache of varying intensity, often accompanied by nausea and sensitivity to light and sound', 'Pain relievers, triptans, preventive medications', 'medium'),
('Hypertension', 'High blood pressure', 'Lifestyle changes, medications to lower blood pressure', 'medium');

-- Sample data for disease_symptoms table
INSERT INTO `disease_symptoms` (`disease_id`, `symptom_id`, `severity`) VALUES
-- Common Cold (1)
(1, 3, 'moderate'), -- Cough
(1, 5, 'moderate'), -- Sore throat
(1, 6, 'severe'),   -- Runny nose
(1, 4, 'mild'),     -- Fatigue

-- Influenza (2)
(2, 1, 'severe'),   -- Fever
(2, 2, 'moderate'), -- Headache
(2, 3, 'moderate'), -- Cough
(2, 4, 'severe'),   -- Fatigue
(2, 7, 'severe'),   -- Muscle ache
(2, 16, 'moderate'), -- Chills

-- COVID-19 (3)
(3, 1, 'moderate'), -- Fever
(3, 3, 'severe'),   -- Cough
(3, 4, 'severe'),   -- Fatigue
(3, 8, 'severe'),   -- Shortness of breath
(3, 17, 'moderate'), -- Loss of appetite

-- Strep Throat (4)
(4, 1, 'moderate'), -- Fever
(4, 5, 'severe'),   -- Sore throat
(4, 2, 'mild'),     -- Headache
(4, 19, 'moderate'), -- Swollen glands

-- Bronchitis (5)
(5, 3, 'severe'),   -- Cough
(5, 8, 'moderate'), -- Shortness of breath
(5, 9, 'mild'),     -- Chest pain
(5, 4, 'moderate'), -- Fatigue

-- Pneumonia (6)
(6, 1, 'severe'),   -- Fever
(6, 3, 'severe'),   -- Cough
(6, 8, 'severe'),   -- Shortness of breath
(6, 9, 'moderate'), -- Chest pain
(6, 4, 'severe'),   -- Fatigue
(6, 16, 'moderate'), -- Chills

-- Sinusitis (7)
(7, 2, 'severe'),   -- Headache
(7, 6, 'moderate'), -- Runny nose
(7, 5, 'mild'),     -- Sore throat

-- Gastroenteritis (8)
(8, 10, 'severe'),  -- Nausea
(8, 11, 'moderate'), -- Vomiting
(8, 12, 'severe'),  -- Diarrhea
(8, 18, 'moderate'), -- Abdominal pain

-- Migraine (9)
(9, 2, 'severe'),   -- Headache
(9, 10, 'moderate'), -- Nausea
(9, 15, 'moderate'), -- Dizziness

-- Hypertension (10)
(10, 2, 'moderate'), -- Headache
(10, 15, 'mild'),   -- Dizziness
(10, 9, 'mild');    -- Chest pain

-- Sample data for disease_specializations table
INSERT INTO `disease_specializations` (`disease_id`, `specialization`) VALUES
(1, 'General Practice'),
(1, 'Family Medicine'),
(2, 'General Practice'),
(2, 'Infectious Disease'),
(3, 'Infectious Disease'),
(3, 'Pulmonology'),
(4, 'Otolaryngology'),
(4, 'General Practice'),
(5, 'Pulmonology'),
(5, 'General Practice'),
(6, 'Pulmonology'),
(6, 'Infectious Disease'),
(7, 'Otolaryngology'),
(7, 'General Practice'),
(8, 'Gastroenterology'),
(8, 'General Practice'),
(9, 'Neurology'),
(9, 'General Practice'),
(10, 'Cardiology'),
(10, 'Internal Medicine'); 