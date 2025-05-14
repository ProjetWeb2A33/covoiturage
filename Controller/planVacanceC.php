<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Hotel.php';
require_once __DIR__ . '/../Controller/HotelC.php';
require_once __DIR__ . '/../Controller/MailC.php';

class PlanVacanceC
{
    public function listPlans() {
        // Let's check the table structure first
        $db = config::getConnexion();
        try {
            // Check if the table exists and its structure
            $checkTable = $db->query("SHOW TABLES LIKE 'plan_vacance'");
            if ($checkTable->rowCount() == 0) {
                echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                        Erreur: La table 'plan_vacance' n'existe pas dans la base de données.
                      </div>";
                return [];
            }

            // Check table columns
            $columns = $db->query("DESCRIBE plan_vacance")->fetchAll(PDO::FETCH_COLUMN);
            error_log("Debug listPlans: Table columns: " . implode(', ', $columns));

            // Now get the data
            $sql = "SELECT * FROM plan_vacance";
            error_log("Debug listPlans: SQL Query: " . $sql);

            $query = $db->query($sql);
            $result = $query->fetchAll(PDO::FETCH_ASSOC); // Retourne un tableau associatif

            // Debug information
            echo "<!-- Debug listPlans: Found " . count($result) . " plans -->";
            if (count($result) > 0) {
                echo "<!-- Debug listPlans: First plan: " . print_r($result[0], true) . " -->";
                echo "<!-- Debug listPlans: Available columns: " . implode(', ', array_keys($result[0])) . " -->";
            } else {
                echo "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                        Aucun plan de vacances trouvé dans la base de données. Veuillez en ajouter un.
                      </div>";
            }

            return $result;
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                    Erreur lors de la récupération des plans: " . $e->getMessage() . "
                  </div>";
            echo "<!-- Debug listPlans Error: " . $e->getMessage() . " -->";
            return []; // Return empty array instead of dying
        }
    }

    public function deletePlan($id)
    {
        $sql = "DELETE FROM plan_vacance WHERE id_plan = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function addPlan($plan)
    {
        // Get database connection first
        $db = config::getConnexion();

        // Debugging: Make sure plan is correctly passed
        echo "<!-- Debug addPlan: Identifiant: " . $plan->getIdentifiant() . " -->";
        echo "<!-- Debug addPlan: Nom Utilisateur: " . $plan->getNomUtilisateur() . " -->";
        echo "<!-- Debug addPlan: Email: " . $plan->getEmail() . " -->";

        // Check if the table exists and its structure
        $checkTable = $db->query("SHOW TABLES LIKE 'plan_vacance'");
        if ($checkTable->rowCount() == 0) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                    Erreur: La table 'plan_vacance' n'existe pas dans la base de données.
                  </div>";
            return false;
        }

        // Set the location column name
        $locationColumn = 'location_voiture';

        // SQL insert statement
        $sql = "INSERT INTO plan_vacance (
                    identifiant,
                    nom_utilisateur,
                    email,
                    date_depart,
                    date_retour,
                    type_transport,
                    $locationColumn,
                    besoin_parking,
                    id_hotel
                ) VALUES (
                    :identifiant, :nu, :email, :dd, :dr, :tt, :lv, :bp, :ih
                )";
        try {
            // Prepare and execute the query
            $query = $db->prepare($sql);

            // Create parameters array
            $params = [
                'identifiant' => $plan->getIdentifiant(),
                'nu' => $plan->getNomUtilisateur(),
                'email' => $plan->getEmail(),
                'dd' => $plan->getDateDepart(),
                'dr' => $plan->getDateRetour(),
                'tt' => $plan->getTypeTransport(),
                'lv' => $plan->getLocationVoiture(),
                'bp' => $plan->getBesoinParking(),
                'ih' => $plan->getIdHotel()
            ];

            // Debug parameters in log file only
            error_log("Debug addPlan: Parameters: " . print_r($params, true));

            $query->execute($params);

            // Optional: Check how many rows were affected
            $rowCount = $query->rowCount();
            error_log("Debug addPlan: Rows inserted: " . $rowCount);

            if ($rowCount > 0) {
                // Get the inserted plan ID
                $planId = $db->lastInsertId();

                // Try to send email notification
                try {
                    // Get hotel name for the email
                    $hotelC = new HotelC();
                    $hotel = $hotelC->showHotel($plan->getIdHotel());
                    $hotelName = $hotel ? $hotel['nom_hotel'] : 'Non spécifié';

                    // Prepare plan data for email
                    $planData = [
                        'id_plan' => $planId,
                        'identifiant' => $plan->getIdentifiant(),
                        'nom_utilisateur' => $plan->getNomUtilisateur(),
                        'email' => $plan->getEmail(),
                        'date_depart' => $plan->getDateDepart(),
                        'date_retour' => $plan->getDateRetour(),
                        'type_transport' => $plan->getTypeTransport(),
                        'location_voiture' => $plan->getLocationVoiture(),
                        'besoin_parking' => $plan->getBesoinParking(),
                        'id_hotel' => $plan->getIdHotel()
                    ];

                    // Send confirmation email
                    $mailC = new MailC();
                    $emailSent = $mailC->sendVacationPlanConfirmation($planData, $hotelName);

                    // Add visible success message
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                            Plan de vacances ajouté avec succès! ID: " . $planId . "
                            " . ($emailSent ? "<br>Un email de confirmation a été envoyé à " . $plan->getEmail() : "<br>Impossible d'envoyer l'email de confirmation") . "
                          </div>";
                } catch (Exception $e) {
                    // Just log the error but don't show it to the user
                    error_log('Email error: ' . $e->getMessage());

                    // Add visible success message without email info
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                            Plan de vacances ajouté avec succès! ID: " . $planId . "
                          </div>";
                }

                return true; // Return true on success
            } else {
                // Add visible warning message
                echo "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                        Avertissement: Aucune ligne n'a été insérée dans la base de données.
                      </div>";
                return false;
            }

        } catch (Exception $e) {
            // Add visible error message
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                    Erreur lors de l'ajout du plan: " . $e->getMessage() . "
                  </div>";
            error_log("Debug addPlan Error: " . $e->getMessage());
            return false; // Return false on error
        }
    }

    public function showPlan($id)
    {
        $sql = "SELECT * FROM plan_vacance WHERE id_plan = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':id', $id);
            $query->execute();
            return $query->fetch();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function updatePlan($plan, $id)
    {
        try {
            $db = config::getConnexion();

            // Set the location column name
            $locationColumn = 'location_voiture';

            $query = $db->prepare(
                "UPDATE plan_vacance SET
                    identifiant = :identifiant,
                    nom_utilisateur = :nu,
                    email = :email,
                    date_depart = :dd,
                    date_retour = :dr,
                    type_transport = :tt,
                    $locationColumn = :lv,
                    besoin_parking = :bp,
                    id_hotel = :ih
                WHERE id_plan = :id"
            );

            $params = [
                'id' => $id,
                'identifiant' => $plan->getIdentifiant(),
                'nu' => $plan->getNomUtilisateur(),
                'email' => $plan->getEmail(),
                'dd' => $plan->getDateDepart(),
                'dr' => $plan->getDateRetour(),
                'tt' => $plan->getTypeTransport(),
                'lv' => $plan->getLocationVoiture(),
                'bp' => $plan->getBesoinParking(),
                'ih' => $plan->getIdHotel()
            ];

            // Debug parameters in log file only
            error_log("Debug updatePlan: Parameters: " . print_r($params, true));

            $query->execute($params);

            // Check if any rows were updated
            $rowsUpdated = $query->rowCount();
            error_log("Debug updatePlan: Rows updated: " . $rowsUpdated);

            if ($rowsUpdated > 0) {
                // Try to send email notification
                try {
                    // Get hotel name for the email
                    $hotelC = new HotelC();
                    $hotel = $hotelC->showHotel($plan->getIdHotel());
                    $hotelName = $hotel ? $hotel['nom_hotel'] : 'Non spécifié';

                    // Prepare plan data for email
                    $planData = [
                        'id_plan' => $id,
                        'identifiant' => $plan->getIdentifiant(),
                        'nom_utilisateur' => $plan->getNomUtilisateur(),
                        'email' => $plan->getEmail(),
                        'date_depart' => $plan->getDateDepart(),
                        'date_retour' => $plan->getDateRetour(),
                        'type_transport' => $plan->getTypeTransport(),
                        'location_voiture' => $plan->getLocationVoiture(),
                        'besoin_parking' => $plan->getBesoinParking(),
                        'id_hotel' => $plan->getIdHotel()
                    ];

                    // Send confirmation email
                    $mailC = new MailC();
                    $emailSent = $mailC->sendVacationPlanConfirmation($planData, $hotelName, true); // true for update

                    // Add visible success message
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                            Plan de vacances mis à jour avec succès!
                            " . ($emailSent ? "<br>Un email de confirmation a été envoyé à " . $plan->getEmail() : "<br>Impossible d'envoyer l'email de confirmation") . "
                          </div>";
                } catch (Exception $e) {
                    // Just log the error but don't show it to the user
                    error_log('Email error: ' . $e->getMessage());

                    // Add visible success message without email info
                    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                            Plan de vacances mis à jour avec succès!
                          </div>";
                }
                return true;
            } else {
                echo "<div style='background-color: #fff3cd; color: #856404; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                        Avertissement: Aucune modification n'a été effectuée.
                      </div>";
                return false;
            }

        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>
                    Erreur lors de la mise à jour du plan: " . $e->getMessage() . "
                  </div>";
            error_log("Debug updatePlan Error: " . $e->getMessage());
            return false;
        }
    }
    public function searchPlansByIdentifiant($identifiant) {
        $sql = "SELECT * FROM plan_vacance WHERE identifiant LIKE :identifiant";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['identifiant' => '%' . $identifiant . '%']);
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Search error: ' . $e->getMessage()); // Log l'erreur pour le débogage
            return []; // Retourne un tableau vide en cas d'erreur
        }
    }
}