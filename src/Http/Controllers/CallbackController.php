<?php


namespace Codewiser\ResourceServer\Http\Controllers;


use Codewiser\ResourceServer\Exceptions\OauthResponseException;
use Codewiser\ResourceServer\Facades\OAuthClient;
use Codewiser\ResourceServer\Facades\ResourceServer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        try {

            OAuthClient::callback($request);

            return redirect(OAuthClient::getReturnUrl('/'));

        } catch (OauthResponseException $e) {
            if ($e->getMessage() == 'access_denied') {
                // User interrupts authorization himself
                // It is not an error
                return redirect(OAuthClient::getReturnUrl('/'));
            }

            echo "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getDescription()) {
                echo "<p>{$e->getDescription()}</p>";
            }
            if ($e->getUri()) {
                echo '<a href="' . $e->getUri() . '">' . $e->getUri() . '</a>';
            }
            echo "<pre>{$e->getResponseBody()}</pre>";
            die();
        } catch (\Throwable $e) {
            echo "Error {$e->getCode()}: {$e->getMessage()}";
            die();
        }
    }
}