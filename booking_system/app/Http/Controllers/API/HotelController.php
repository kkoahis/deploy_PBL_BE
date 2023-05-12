<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\HotelResource;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Controllers\API\BaseController as BaseController;

class HotelController extends BaseController
{
    //
    public function index()
    {
        $hotel = Hotel::paginate(20);
        return response()->json(([
            'success' => true,
            'message' => 'Hotel retrieved successfully.',
            'data' => $hotel,
        ]
        ));
    }

    public function show($id)
    {
        $hotel = Hotel::find($id);

        if (is_null($hotel)) {
            return $this->sendError('Hotel not found.');
        }

        return $this->sendResponse(new HotelResource($hotel), 'Hotel retrieved successfully.');
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'address' => 'required',
            'hotline' => 'required',
            'email' => 'required',
            'description',
            'room_total' => 'required',
            'parking_slot' => 'required',
            'bathrooms' => 'required',
            // create_by has to be user admin in table users
            'created_by' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $user = User::find($input['created_by']);
        if (is_null($user)) {
            return $this->sendError('User is not found.');
        }
        if ($user->role != 'admin') {
            return $this->sendError('User is not admin.');
        }
        if ($user->role == 'admin') {
            $hotel = Hotel::create($input);
            return $this->sendResponse(new HotelResource($hotel), 'Hotel created successfully.');
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name',
            'address',
            'hotline',
            'email',
            'description',
            'room_total',
            'parking_slot',
            'bathrooms',
            // create_by user admin
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $hotel = Hotel::find($id);
        if (is_null($hotel)) {
            return $this->sendError('Hotel not found.');
        }
        $hotel->name = $input['name'];
        $hotel->address = $input['address'];
        $hotel->hotline = $input['hotline'];
        $hotel->email = $input['email'];
        $hotel->description = $input['description'];
        $hotel->room_total = $input['room_total'];
        $hotel->parking_slot = $input['parking_slot'];
        $hotel->bathrooms = $input['bathrooms'];
        if ($hotel->save()) {
            return response()->json(([
                    'success' => true,
                    'message' => 'Hotel updated successfully.',
                    'data' => $hotel,
                ])
            );
        } else {
            return $this->sendError('Hotel not updated.');
        }
    }

    public function destroy($id)
    {
        $hotel = Hotel::find($id);
        if (is_null($hotel)) {
            return $this->sendError('Hotel not found.');
        }

        $category = $hotel->category;
        $hotelImage = $hotel->hotelImage;
        $room = $hotel->category->map(function ($category) {
            return $category->room;
        });
        $roomImage = $hotel->category->map(function ($category) {
            return $category->room->map(function ($room) {
                return $room->roomImage;
            });
        });
        $booking = $hotel->booking;

        // only get review != null
        $review = $hotel->booking->map(function ($booking) {
            return $booking->review;
        })->filter(function ($review) {
            return !is_null($review);
        });

        // only get reply != null
        $reply = $review->map(function ($review) {
            return $review->reply;
        })->filter(function ($reply) {
            return !is_null($reply);
        });

        // echo $reply;
        // echo $room;  
        // echo $roomImage;
        // echo $review;

        // if hotel delete, category, room and hotel image will update deleted_at
        if ($hotel->delete()) {
            foreach ($category as $cat) {
                $cat->delete();
            }
            foreach ($room as $r) {
                foreach ($r as $ro) {
                    $ro->delete();
                }
            }
            foreach ($hotelImage as $hi) {
                $hi->delete();
            }
            foreach ($roomImage as $ri) {
                foreach ($ri as $r) {
                    foreach ($r as $ro) {
                        $ro->delete();
                    }
                }
            }
            foreach ($booking as $b) {
                $b->delete();
            }

            // delete review, if review has null value, it will not delete
            foreach ($review as $r) {
                $r->delete();
            }

            // delete reply, if reply has null value, it will not delete
            foreach ($reply as $rp) {
                foreach ($rp as $r) {
                    $r->delete();
                }
            }
        } else {
            return $this->sendError('Hotel not deleted.');
        }

        return $this->sendResponse([], 'Hotel deleted successfully.');
    }


    public function restore($id)
    {
        // if hotel restore, category, room, room image and hotel image will update deleted_at
        $hotel = Hotel::onlyTrashed()->find($id);

        if (is_null($hotel)) {
            return $this->sendError('Hotel not found.');
        }

        $category = $hotel->category()->onlyTrashed()->get();
        $hotelImage = $hotel->hotelImage()->onlyTrashed()->get();
        $room = $hotel->category()->onlyTrashed()->get()->map(function ($category) {
            return $category->room()->onlyTrashed()->get();
        });
        $roomImage = $hotel->category()->onlyTrashed()->get()->map(function ($category) {
            return $category->room()->onlyTrashed()->get()->map(function ($room) {
                return $room->roomImage()->onlyTrashed()->get();
            });
        });
        $booking = $hotel->booking()->onlyTrashed()->get();
        $review = $hotel->booking()->onlyTrashed()->get()->map(function ($booking) {
            return $booking->review()->onlyTrashed()->get();
        });
        $reply = $hotel->booking()->onlyTrashed()->get()->map(function ($booking) {
            return $booking->review()->onlyTrashed()->get()->map(function ($review) {
                return $review->reply()->onlyTrashed()->get();
            });
        });

        // echo $category . "<br>";
        // echo $hotelImage . "<br>";
        // echo $room . "<br>";
        // echo $roomImage . "<br>";

        // restore hotel
        if ($hotel->restore()) {
            // restore category
            foreach ($category as $cat) {
                $cat->restore();
            }
            // restore room
            foreach ($room as $r) {
                foreach ($r as $ro) {
                    $ro->restore();
                }
            }
            // restore hotel image
            foreach ($hotelImage as $hi) {
                $hi->restore();
            }
            // restore room image
            foreach ($roomImage as $ri) {
                foreach ($ri as $r) {
                    foreach ($r as $ro) {
                        $ro->restore();
                    }
                }
            }

            // restore booking
            foreach ($booking as $b) {
                $b->restore();
            }
            // restore review
            foreach ($review as $r) {
                foreach ($r as $ro) {
                    $ro->restore();
                }
            }
            // restore reply
            foreach ($reply as $rp) {
                foreach ($rp as $r) {
                    foreach ($r as $ro) {
                        $ro->restore();
                    }
                }
            }

            return $this->sendResponse([], 'Hotel restored successfully.');
        } else {
            return $this->sendError('Hotel not restored.');
        }
    }
}
