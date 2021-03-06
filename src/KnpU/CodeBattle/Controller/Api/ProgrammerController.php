<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Api\ApiProblem;
use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', [$this, 'newAction']);
        $controllers->get('/api/programmers/{nickname}', [$this, 'showAction'])->bind('api_programmers_show');
        $controllers->get('/api/programmers', [$this, 'listAction']);
        $controllers->put('/api/programmers/{nickname}', [$this, 'updateAction']);
        $controllers->delete('/api/programmers/{nickname}', [$this, 'deleteAction']);
        $controllers->match('/api/programmers/{nickname}', [$this, 'updateAction'])->method('PATCH');
    }

    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $programmer = new Programmer($data['nickname'], $data['avatarNumber']);
        $this->handleRequest($request, $programmer);
        if ($errors = $this->validate($programmer)) {
            //return $this->handleValidationResponse($errors);
            $this->throwApiProblemValidationException($errors);
        }
        $this->save($programmer);

        //$data = $this->serializeProgrammer($programmer);
        //$json = $this->serialize($programmer);
        //$response = new Response('It worked. Believe me - I\'m an API', 201);
        //$response = new Response(json_encode($data), 201);
        //$response = new JsonResponse($json, 201);
        //$response = new Response($json, 201);
        //$response->headers->set('Location', '/some/programmer/url');
        $response = $this->createApiResponse($programmer, 201);
        $programmerUrl = $this->generateUrl(
            'api_programmers_show',
            ['nickname' => $programmer->nickname]
        );
        $response->headers->set('Location', $programmerUrl);
        //$response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function showAction($nickname)
    {
        //throw new \Exception('I made a mistake!');

        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);
        if (!$programmer) {
            $this->throw404('Crap! This programmer has deserted! We\'ll send a search party');
        }
        // $data = [
        //     'nickname' => $programmer->nickname,
        //     'avatarNumber' => $programmer->avatarNumber,
        //     'powerLevel' => $programmer->powerLevel,
        //     'tagLine' => $programmer->tagLine,
        // ];
        //$data = $this->serializeProgrammer($programmer);
        //$json = $this->serialize($programmer);
        //$response = new Response(json_encode($data), 200);
        //$response = new Response($json, 200);
        $response = $this->createApiResponse($programmer, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()->findAll();
        // $data = ['programmers' => []];
        // foreach ($programmers as $programmer) {
        //     $data['programmers'][] = $this->serializeProgrammer($programmer);
        // }
        $data = ['programmers' => $programmers];
        //$json = $this->serialize($data);
        // $response = new Response(json_encode($data), 200);
        //$response = new Response($json, 200);
        $response = $this->createApiResponse($data, 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function updateAction($nickname, Request $request)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404();
        }

        $this->handleRequest($request, $programmer);
        if ($errors = $this->validate($programmer)) {
            //return $this->handleValidationResponse($errors);
            $this->throwApiProblemValidationException($errors);
        }
        $this->save($programmer);

        //$data = $this->serializeProgrammer($programmer);
        //$json = $this->serialize($programmer);
        //$response = new JsonResponse($json, 200);
        //$response = new Response($json, 200);
        $response = $this->createApiResponse($programmer, 200);

        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()->findOneByNickname($nickname);

        if ($programmer) {
            $this->delete($programmer);
        }

        return new Response(null, 204);
    }

    private function hthrowApiProblemValidationException(array $errors)
    {
        // $data = [
        //     'type' => 'validation_error',
        //     'title' => 'There was a validation error',
        //     'errors' => $errors
        // ];
        $apiProblem = new ApiProblem(400, ApiProblem::TYPE_VALIDATION_ERROR);
        $apiProblem->set('errors', $errors);

        //$response = new JsonResponse($data, 400);
        // $response = new JsonResponse($apiProblem->toArray(), $apiProblem->getStatusCode());
        // $response->headers->set('Content-Type', 'application/problem+json');

        // return $response;
        throw new ApiProblemException($apiProblem);
    }

    private function handleRequest(Request $request, Programmer $programmer)
    {
        $data = json_decode($request->getContent(), true);
        $isNew = !$programmer->id;
        if ($data === null) {
            //throw new \Exception(sprintf('Invalid JSON: '.$request->getContent()));
            //throw new HttpException(400, sprintf('Invalid JSON: '.$request->getContent()));
            $problem = new ApiProblem(400, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);
            throw new ApiProblemException($problem);
        }
        // $programmer->nickname = $data['nickname'];
        // $programmer->avatarNumber = $data['avatarNumber'];
        // $programmer->tagLine = $data['tagLine'];
        //$apiProperties = ['nickname', 'avatarNumber', 'tagLine'];
        $apiProperties = ['avatarNumber', 'tagLine'];
        if ($isNew) {
            $apiProperties[] = 'nickname';
        }
        foreach ($apiProperties as $property) {
            if (!isset($data[$property]) && $request->isMethod('PATCH')) {
                continue;
            }
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }
        $programmer->userId = $this->findUserByUsername('weaverryan')->id;
    }

    // private function serializeProgrammer(Programmer $programmer)
    // {
    //     return [
    //         'nickname' => $programmer->nickname,
    //         'avatarNumber' => $programmer->avatarNumber,
    //         'powerLevel' => $programmer->powerLevel,
    //         'tagLine' => $programmer->tagLine,
    //     ];
    // }

    // protected function serialize($data)
    // {
    //     return $this->container['serializer']->serialize($data, 'json');
    // }
}
