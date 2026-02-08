<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Policies\V1\TicketPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AuthorTicketsController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    public function index($author_id, TicketFilter $filters)
    {
        $tickets = Ticket::where('user_id', $author_id)
            ->filter($filters)
            ->paginate();

        return TicketResource::collection($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, $author_id)
    {
        try {
            //policy
            $this->isAble('store', Ticket::class);

            return new TicketResource(Ticket::create($request->mappedAttributes([
                'author' => 'user_id'
            ])));
        } catch (AuthorizationException $ex) {
            return $this->error(
                'You are not authorize to update this resource',
                401
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $author_id)
                ->findOrFail();

            $this->isAble('delete', $ticket);
            $ticket->delete();

            return $this->ok('Ticket successfully delleted');
        } catch (ModelNotFoundException $exception) {
            return $this->error('Ticket not found', 404);
        } catch (AuthorizationException $ex) {
            return $this->error(
                'You are not authorize to delete this resource',
                401
            );
        }
    }

    public function replace(ReplaceTicketRequest $request, int $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $author_id)
                ->findOrFail();

            $this->isAble('replace', $ticket);

            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);

            // TODO: ticket doesn't belong to user

        } catch (ModelNotFoundException $exception) {
            return $this->ok(
                'Ticket not found',
                [
                    'error' => 'The provided ticket id does not exists'
                ]
            );
        } catch (AuthorizationException $ex) {
            return $this->error(
                'You are not authorize to update this resource',
                401
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, int $author_id, $ticket_id)
    {
        // PATCH
        try {
            $ticket = Ticket::where('id', $ticket_id)
                ->where('user_id', $author_id)
                ->findOrFail();

            $this->isAble('update', $ticket);

            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        } catch (ModelNotFoundException $exception) {
            return $this->ok(
                'Ticket not found',
                ['error' => 'The provided ticket id does not exists']
            );
        } catch (AuthorizationException $ex) {
            return $this->error(
                'You are not authorize to update this resource',
                401
            );
        }
    }
}
