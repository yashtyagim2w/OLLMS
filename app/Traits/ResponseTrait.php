<?php

namespace App\Traits;

/**
 * ResponseTrait
 * 
 * DRY helper for consistent JSON and redirect responses across controllers
 * All errors/success messages work with SwalHelper on the frontend
 */
trait ResponseTrait
{
    /**
     * Return JSON success response
     * 
     * @param string $message Success message
     * @param array $data Additional data
     * @param int $code HTTP status code
     */
    protected function jsonSuccess(string $message, array $data = [], int $code = 200)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Return JSON error response
     * 
     * @param string $message Error message
     * @param array $errors Validation errors or additional error details
     * @param int $code HTTP status code
     */
    protected function jsonError(string $message, array $errors = [], int $code = 400)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ]);
    }

    /**
     * Redirect with error message (picked up by SWAL)
     * 
     * @param string $url Redirect URL
     * @param string $message Error message
     */
    protected function redirectWithError(string $url, string $message)
    {
        return redirect()->to($url)->with('error', $message);
    }

    /**
     * Redirect with success message (picked up by SWAL)
     * 
     * @param string $url Redirect URL
     * @param string $message Success message
     */
    protected function redirectWithSuccess(string $url, string $message)
    {
        return redirect()->to($url)->with('message', $message);
    }

    /**
     * Redirect back with error message
     */
    protected function backWithError(string $message)
    {
        return redirect()->back()->with('error', $message)->withInput();
    }

    /**
     * Redirect back with validation errors
     */
    protected function backWithErrors(array $errors)
    {
        return redirect()->back()->with('errors', $errors)->withInput();
    }

    /**
     * Redirect back with success message
     */
    protected function backWithSuccess(string $message)
    {
        return redirect()->back()->with('message', $message);
    }
}
