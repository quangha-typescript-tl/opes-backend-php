<?php

namespace App\Http\Controllers;

use App\Content;
use App\HashTag;
use App\HashTagContent;
use Carbon\Carbon;
use JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;

class ContentController extends Controller
{
    public function getContents()
    {
        $login_user = JWTAuth::parseToken()->authenticate();
        $contents = Content::getContents($login_user->id);

        if ($contents) {
            foreach ($contents as $content) {
                $hashTag = HashTagContent::select('hash_tag')->where('content_id', $content->id)->get();
                if ($hashTag) {
                    $content->created_at = Carbon::parse($content->created_at)->format('Y-m-d\TH:i:sP');
                    $content->updated_at = Carbon::parse($content->updated_at)->format('Y-m-d\TH:i:sP');
                    $content->hash_tag = $hashTag;
                } else {
                    $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                    $message = trans('get content fail');
                    return response()->json($message, $status);
                }
            }

            $result = [
                'contents' => $contents
            ];
            $status = config('constants.http_status.HTTP_GET_SUCCESS');
            return response()->json($result, $status);
        } else {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('get content fail');
            return response()->json($message, $status);
        }
    }

    public function getDetailContent($contentId)
    {
        $content = Content::find($contentId);
        if ($content) {

            $hashTag = HashTagContent::select('hash_tag')->where('content_id', $content->id)->get();
            if ($hashTag) {
                $content->created_at = Carbon::parse($content->created_at)->format('Y-m-d\TH:i:sP');
                $content->updated_at = Carbon::parse($content->updated_at)->format('Y-m-d\TH:i:sP');
                $content->hash_tag = $hashTag;

                $result = [
                    'content' => $content
                ];
                $status = config('constants.http_status.HTTP_GET_SUCCESS');
                return response()->json($result, $status);
            } else {
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('get content fail');
                return response()->json($message, $status);
            }
        } else {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('get content fail');
            return response()->json($message, $status);
        }
    }

    public function addContent()
    {
        $validate =  Validator::make(Request::all(), [
            'title' => 'required|max:150',
            'description' => 'required',
            'public' => 'required'
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('title is null');
            return response()->json($message, $status);
        }

        $login_user = JWTAuth::parseToken()->authenticate();

        $content = new Content();
        $content->title = Request::input('title');
        $content->description = Request::input('description');
        $content->public = Request::input('public');
        $content->content = Request::input('content');
        $content->user_created = $login_user->id;
        $content->image = Request::input('image');
        $content->created_at = Carbon::now()->format('Y-m-d H:i:s');
        $content->updated_at = Carbon::now()->format('Y-m-d H:i:s');

        $hashTag = Request::input('hash_tag');

        DB::beginTransaction();

        try {
            $result = $content->save();
            if ($result) {

                foreach ($hashTag as $tag) {
                    // add hash_tag
                    $hashTagSearch = HashTag::where('hash_tag', $tag['hash_tag'])->first();
                    if (!$hashTagSearch) {
                        $hashTagNew = new HashTag();
                        $hashTagNew->hash_tag = $tag['hash_tag'];
                        $hashTagSave = $hashTagNew->save();

                        if (!$hashTagSave) {
                            DB::rollback();
                            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                            $message = trans('add content fail');
                            return response()->json($message, $status);
                        }
                    }

                    // add hash_tag_content
                    $hashTagContentNew = new HashTagContent();
                    $hashTagContentNew->hash_tag = $tag['hash_tag'];
                    $hashTagContentNew->content_id = $content['id'];
                    $hashTagContentSave = $hashTagContentNew->save();

                    if (!$hashTagContentSave) {
                        DB::rollback();
                        $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        $message = trans('add content fail');
                        return response()->json($message, $status);
                    }
                }

                DB::commit();
                $status = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = trans('add content success');
                return response()->json($message, $status);
            } else {
                DB::rollback();
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('add content fail');
                return response()->json($message, $status);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('add content fail');
            return response()->json($message, $status);
        }
    }

    public function uploadImageContent()
    {
        $file = Request::file('image');
        if ($file) {
            $file->storeAs('/public/image-content', $file->getClientOriginalName());
        }
    }

    public function editContent()
    {
        $validate =  Validator::make(Request::all(), [
            'id' => 'required',
            'title' => 'required|max:150',
            'description' => 'required',
            'public' => 'required'
        ]);

        if ($validate->fails()) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('title is null');
            return response()->json($message, $status);
        }

        $content = Content::find(Request::input('id'));

        if (!$content) {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('content not found');
            return response()->json($message, $status);
        }

        $imageContentOld = $content->image;

        $content->title = Request::input('title');
        $content->description = Request::input('description');
        $content->public = Request::input('public');
        $content->content = Request::input('content');
        $content->image = Request::input('image');
        $content->updated_at = Carbon::now()->format('Y-m-d H:i:s');

        DB::beginTransaction();
        try {
            $result = $content->save();
            if ($result) {
                if ($imageContentOld) {
                    if (Storage::exists('public/image-content/' . $imageContentOld)) {
                        Storage::delete('public/image-content/' . $imageContentOld);
                    }
                }

                // delete hash_tag old
                $hashTags = HashTagContent::where('content_id', $content->id)->get();
                foreach ($hashTags as $hashTag) {
                    $hashTagOld = $hashTag->delete();

                    if (!$hashTagOld) {
                        DB::rollback();
                        $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        $message = trans('edit content fail');
                        return response()->json($message, $status);
                    }
                }
                $hashTagNews = Request::input('hash_tag');
                foreach ($hashTagNews as $hashTag) {

                    // add hash_tag
                    $hashTagSearch = HashTag::where('hash_tag', $hashTag['hash_tag'])->first();
                    if (!$hashTagSearch) {
                        $hashTagNew = new HashTag();
                        $hashTagNew->hash_tag = $hashTag['hash_tag'];
                        $hashTagSave = $hashTagNew->save();

                        if (!$hashTagSave) {
                            DB::rollback();
                            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                            $message = trans('edit content fail');
                            return response()->json($message, $status);
                        }
                    }

                    // add hash_tag_content new
                    $hashTagNew = new HashTagContent();
                    $hashTagNew->hash_tag = $hashTag['hash_tag'];
                    $hashTagNew->content_id = $content->id;
                    $hashTagNewSave = $hashTagNew->save();

                    if (!$hashTagNewSave) {
                        DB::rollback();
                        $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        $message = trans('edit content fail');
                        return response()->json($message, $status);
                    }
                }

                DB::commit();
                $status = config('constants.http_status.HTTP_POST_SUCCESS');
                $message = trans('edit content success');
                return response()->json($message, $status);
            } else {
                DB::rollback();
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('edit content fail');
                return response()->json($message, $status);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $code    = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = 'edit content fail';
            return response()->json($message, $code);
        }
    }

    public function deleteContent()
    {
        $contentId = Request::input('contentId');
        $content = Content::find($contentId);

        if ($content) {
            DB::beginTransaction();
            try {
                $result = $content->delete();

                if ($result) {
                    $image = $content->image;
                    if ($image) {
                        if (Storage::exists('public/image-content/' . $image)) {
                            Storage::delete('public/image-content/' . $image);
                        }
                    }

                    $hashTagContent = HashTagContent::where('content_id', $contentId)->get();
                    if ($hashTagContent) {

                        foreach ($hashTagContent as $hashTag) {
                            $hashTagContentDelete = $hashTag->delete();
                            if (!$hashTagContentDelete) {
                                DB::rollback();
                                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                                $message = trans('delete content fail');
                                return response()->json($message, $status);
                            }
                        }

                    } else {
                        DB::rollback();
                        $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                        $message = trans('delete content fail');
                        return response()->json($message, $status);
                    }

                    DB::commit();
                    $status = config('constants.http_status.HTTP_POST_SUCCESS');
                    $message = trans('delete content success');
                    return response()->json($message, $status);
                } else {
                    DB::rollback();
                    $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                    $message = trans('delete content fail');
                    return response()->json($message, $status);
                }
            } catch (\Exception $e) {
                DB::rollback();
                $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
                $message = trans('delete content fail');
                return response()->json($message, $status);
            }

        } else {
            $status = config('constants.http_status.HTTP_INTERNAL_SERVER_ERROR');
            $message = trans('not found content');
            return response()->json($message, $status);
        }
    }

    public function getTopContentRelated()
    {
        $contentId = Request::input('contentId');
        $limitContentRelated = 10;
        $contents = [];

        if ($contentId) {
            $content = Content::find($contentId);

            if ($content) {
                $contents = $this->contentRelated($content, $limitContentRelated);
            } else {
                $contents = $this->getTopContent($limitContentRelated);
            }
        } else {
            $contents = $this->getTopContent($limitContentRelated);
        }

        $result = ['contentRelated' => $contents];
        $status = config('constants.http_status.HTTP_GET_SUCCESS');
        return response()->json($result, $status);
    }

    public function contentRelated($content, $limitContentRelated) {
        $loginUser = JWTAuth::parseToken()->authenticate();
        $contents = Content::getTopContentRelated($content, $loginUser, $limitContentRelated);

        if ($contents) {
            if (count($contents) < $limitContentRelated) {
                $listContentId = [];
                array_push($listContentId, $content->id);
                foreach ($contents as $co) {
                    array_push($listContentId, $co->id);
                }
                $contentsTop = Content::getTopContentsSearch($loginUser->id, $limitContentRelated - count($contents), $listContentId);
                if ($contentsTop) {
                    $list = [];
                    foreach ($contentsTop as $co) {
                        $contentNew = ['id' => $co->id, 'title' => $co->title];
                        array_push($list, $contentNew);
                    }
                    foreach ($contents as $co) {
                        $contentNew = ['id' => $co->id, 'title' => $co->title];
                        array_push($list, $contentNew);
                    }
                    return $list;
                }
            }
        } else {
            $contents = $this->getTopContent($limitContentRelated);
        }

        return $contents;
    }

    public function getTopContent($limitContentRelated)
    {
        $loginUser = JWTAuth::parseToken()->authenticate();
        $contents = Content::getTopContents($loginUser->id, $limitContentRelated);

        if ($contents) {
            return $contents;
        } else {
            return [];
        }
    }
}
