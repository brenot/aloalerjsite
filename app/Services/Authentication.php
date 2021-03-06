<?php
namespace App\Services;

use App\Data\Repositories\Users;
use App\Services\Traits\RemoteRequest;
use App\Data\Repositories\Users as UsersRepository;
use Illuminate\Support\Facades\Log;

class Authentication
{
    /**
     * @var Guzzle
     */
    protected $guzzle;

    protected $remoteRequest;

    /**
     * @var Users
     */
    protected $usersRepository;

    public function __construct(Users $usersRepository, RemoteRequest $remoteRequest)
    {
        $this->usersRepository = $usersRepository;

        $this->remoteRequest = $remoteRequest;
    }

    public function attempt($request, $remember)
    {
        return $this->loginUser($request, $this->loginRequest($request), $remember);
    }

    protected function extractUsernameFromEmail($email)
    {
        if (($pos = strpos($email, '@')) === false) {
            return $email;
        }

        return substr($email, 0, $pos);
    }

    protected function instantiateGuzzle()
    {
        $this->guzzle = new Guzzle();
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    protected function loginRequest($request)
    {
        if (config('auth.authentication.mock')) {
            return $this->mockedAuthentication(extract_credentials($request));
        }

        try {
            $response = $this->remoteRequest->post(
                config('auth.remote.login.url'),
                extract_credentials($request)
            );
            return $response;
        } catch (\Exception $exception) {
            \Log::error(
                'Exception ao tentar fazer login do usuário ' .
                    extract_credentials($request)['username']
            );
            \Log::error($exception);

            //Timeout no login
            $usersRepository = app(UsersRepository::class);
            $user = $usersRepository->findByColumn(
                'username',
                extract_credentials($request)['username']
            );

            if (is_null($user)) {
                //Sistema de login fora do ar e usuário novo
                Log::error(
                    'O usuário ' .
                        extract_credentials($request)['username'] .
                        ' tentou fazer login, mas não foi possível pois o SGUS está fora do ar e não há histórico do usuário no banco de dados'
                );
                abort(403);
            } else {
                //Usuário já cadastrado
                if (\Hash::check(extract_credentials($request)['password'], $user->password)) {
                    //Credenciais de login conferem com as salvas no banco
                    return $this->mockedAuthentication(extract_credentials($request));
                } else {
                    //Credenciais de login não conferem com as salvas no banco
                    return $this->failedAuthentication();
                }
            }
        }
    }

    /**
     * @param $request
     * @param $response
     * @param $remember
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function loginUser($request, $response, $remember)
    {
        if ($success = $response['success']) {
            $request->merge(['name' => $response['data']['name'][0]]);
            $success = $this->usersRepository->loginUser($request, $remember);

            if (!$success) {
                return false;
            }

            $permissions = app(Authorization::class)->syncUserPermissions(
                extract_credentials($request)['username']
            );

            $this->usersRepository->updateCurrentUserTypeViaPermissions($permissions);
        }

        return $success;
    }

    /**
     * @param $credentials
     *
     * @return array
     */
    protected function mockedAuthentication($credentials)
    {
        return [
            'success' => true,
            'code' => 200,
            'message' => null,
            'data' => [
                'name' => [$credentials['username']],
                'email' => [$credentials['username'] . '@alerj.rj.gov.br'],
                'memberof' => [
                    'CN=ProjEsp,OU=SDGI,OU=Departamentos,OU=ALERJ,DC=alerj,DC=gov,DC=br',
                    'CN=SDGI,OU=SDGI,OU=Departamentos,OU=ALERJ,DC=alerj,DC=gov,DC=br',
                ],
                'description' => ['matricula: N/C'],
            ],
        ];
    }

    /**
     *
     * @return array
     */
    protected function failedAuthentication()
    {
        return [
            'success' => false,
            'code' => 401,
            'message' => 'Attempt failed.',
            'data' => [],
        ];
    }
}
