<?php

namespace App\Controllers;

use CodeIgniter\Shield\Controllers\RegisterController as ShieldRegisterController;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserProfileModel;
use App\Traits\ResponseTrait;

/**
 * Custom Registration Controller
 * 
 * Extends Shield's RegisterController to add:
 * - Server-side validation for names and age
 * - Automatic user_profile creation
 */
class RegisterController extends ShieldRegisterController
{
    use ResponseTrait;

    /**
     * Override the register action to add custom logic
     */
    public function registerAction(): RedirectResponse
    {
        // Get form data
        $firstName = trim($this->request->getPost('first_name') ?? '');
        $lastName = trim($this->request->getPost('last_name') ?? '');
        $dob = $this->request->getPost('dob') ?? '';
        $email = trim($this->request->getPost('email') ?? '');

        // CUSTOM VALIDATION
        // Validate first name
        if (empty($firstName) || strlen($firstName) < 2 || strlen($firstName) > 100) {
            return redirect()->back()
                ->with('error', 'First name must be between 2 and 100 characters.')
                ->withInput();
        }

        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $firstName)) {
            return redirect()->back()
                ->with('error', 'First name can only contain letters, spaces, hyphens, and apostrophes.')
                ->withInput();
        }

        // Validate last name
        if (empty($lastName) || strlen($lastName) < 2 || strlen($lastName) > 100) {
            return redirect()->back()
                ->with('error', 'Last name must be between 2 and 100 characters.')
                ->withInput();
        }

        if (!preg_match('/^[a-zA-Z\s\'-]+$/', $lastName)) {
            return redirect()->back()
                ->with('error', 'Last name can only contain letters, spaces, hyphens, and apostrophes.')
                ->withInput();
        }

        // Validate DOB and age
        if (empty($dob)) {
            return redirect()->back()
                ->with('error', 'Date of birth is required.')
                ->withInput();
        }

        // Parse DOB (format: DD/MM/YYYY)
        $dobParts = explode('/', $dob);
        if (count($dobParts) !== 3) {
            return redirect()->back()
                ->with('error', 'Invalid date format. Please use DD/MM/YYYY.')
                ->withInput();
        }

        $dobDate = \DateTime::createFromFormat('d/m/Y', $dob);
        if (!$dobDate) {
            return redirect()->back()
                ->with('error', 'Invalid date of birth.')
                ->withInput();
        }

        // Calculate age
        $now = new \DateTime();
        $age = $now->diff($dobDate)->y;

        if ($age < 18) {
            return redirect()->back()
                ->with('error', 'You must be at least 18 years old to register.')
                ->withInput();
        }

        // Check if email already exists
        $users = auth()->getProvider();
        $existingUser = $users->findByCredentials(['email' => $email]);

        if ($existingUser !== null) {
            return redirect()->back()
                ->with('error', 'This email is already registered. Please login or use a different email.')
                ->withInput();
        }

        // STORE PROFILE DATA IN SESSION (to create after Shield registration)
        session()->set('pending_profile', [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'dob'        => $dobDate->format('Y-m-d'),
        ]);

        // PROCEED WITH SHIELD REGISTRATION
        $result = parent::registerAction();

        // CREATE USER PROFILE AFTER REGISTRATION
        // Try to get user - Shield stores in session even before full activation
        $userId = null;

        // First try from auth
        if (auth()->loggedIn()) {
            $userId = auth()->user()->id;
        }

        // If not, try from session (Shield stores user in session)
        if (!$userId) {
            $sessionUser = session('user');
            if (is_array($sessionUser) && isset($sessionUser['id'])) {
                $userId = $sessionUser['id'];
            } elseif (is_object($sessionUser) && isset($sessionUser->id)) {
                $userId = $sessionUser->id;
            }
        }

        // Try to find user by email as last resort
        if (!$userId) {
            $newUser = $users->findByCredentials(['email' => $email]);
            if ($newUser) {
                $userId = $newUser->id;
            }
        }

        // Create profile if we found the user
        if ($userId) {
            $pendingProfile = session('pending_profile');
            if ($pendingProfile) {
                $profileModel = new UserProfileModel();

                // Check if profile doesn't already exist
                if (!$profileModel->getByUserId($userId)) {
                    $profileModel->createForUser($userId, $pendingProfile);

                    // Send welcome email
                    $this->sendWelcomeEmail($email, $pendingProfile['first_name']);
                }

                // Clear from session
                session()->remove('pending_profile');
            }
        }

        return $result;
    }

    /**
     * Send welcome email after registration
     */
    protected function sendWelcomeEmail(string $email, string $firstName): void
    {
        try {
            $emailService = service('email');
            $emailService->setTo($email);
            $emailService->setSubject('Welcome to OLLMS - Registration Successful');
            $emailService->setMessage(view('emails/welcome', [
                'firstName' => $firstName,
                'email' => $email,
            ]));

            $emailService->send();
        } catch (\Exception $e) {
            log_message('error', 'Failed to send welcome email: ' . $e->getMessage());
        }
    }
}
