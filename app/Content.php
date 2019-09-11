<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    //    use Notifiable;
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'description', 'public', 'content', 'image', 'user_created', 'created_at', 'updated_at'
    ];

    public static function getContents($userId, $conditionSearch) {
//        $result = Content::where('public', 1)
//            ->orWhere(function ($query) use ($userId) {
//                $query->where('public', 0)->where('user_created', $userId);
//            })
//            ->orderBy('updated_at', 'DESC')->get();
        $result = Content::leftJoin('hash_tag_content', 'hash_tag_content.content_id', '=', 'contents.id')
            ->where(function ($query) use ($conditionSearch) {
                if (count($conditionSearch['hash_tag']) > 0) {
                    $query->whereIn('hash_tag_content.hash_tag', $conditionSearch['hash_tag']);
                }
            })
            ->where(function ($query) use ($conditionSearch) {
                switch ($conditionSearch['typeDatePost']) {
                    case 6:
                        $query->whereDate('contents.created_at', $conditionSearch['dateStart']);
                        break;
                    case 1: // before
                        $query->whereDate('contents.created_at', '<', $conditionSearch['dateStart']);
                        break;
                    case 2:
                        $query->whereDate('contents.created_at', '<=', $conditionSearch['dateStart']);
                        break;
                    case 3:
                        $query->whereDate('contents.created_at', '>', $conditionSearch['dateStart']);
                        break;
                    case 4:
                        $query->whereDate('contents.created_at', '>=', $conditionSearch['dateStart']);
                        break;
                    case 5:
                        $query->whereDate('contents.created_at', '>=', $conditionSearch['dateStart'])
                            ->whereDate('contents.created_at', '<=', $conditionSearch['dateEnd']);
                        break;
                    default:
                        break;
                }
            })
            ->where(function ($query) use ($userId) {
                $query->where('public', 0)->where('user_created', $userId)
                ->orWhere('public', 1);
            })
            ->select('contents.*')
            ->distinct('contents.id')
            ->orderBy('updated_at', 'DESC')->get();
        return $result;
    }

    public static function getTopContentRelated($content, $loginUser, $limitContentRelated) {
        $hashTags = [];
        $hashTag = HashTagContent::where('content_id', $content->id)->get();
        if ($hashTag) {
            foreach ($hashTag as $tag) {
                array_push($hashTags, $tag->hash_tag);
            }
        }

        $result = Content::leftJoin('hash_tag_content', 'hash_tag_content.content_id', '=', 'contents.id')
            ->where(function ($query) use ($loginUser) {
                $query->where(function ($q) use ($loginUser) {
                    $q->where('contents.public', 0)->where('contents.user_created', $loginUser->id);
                })->orWhere('contents.public', 1);
            })
            ->where('contents.id', '<>', $content->id)
            ->where(function ($query) use ($hashTags){
                if (count($hashTags) > 0) {
                    $query->whereIn('hash_tag_content.hash_tag', $hashTags);
                }
            })
            ->select('contents.id', 'contents.title')
            ->orderBy('contents.updated_at', 'DESC')
            ->limit($limitContentRelated)
            ->get();

        return $result;
    }

    public static function getTopContentsSearch($userId, $limitContent, $listContentId) {
        $result = Content::where(function ($query) use ($userId) {
                $query->where(function ($q) use ($userId) {
                    $q->where('public', 0)->where('user_created', $userId);
                })->orWhere('public', 1);
            })
            ->whereNotIn('id', $listContentId)
            ->select('id', 'title')
            ->orderBy('updated_at', 'DESC')->limit($limitContent)->get();
        return $result;
    }

    public static function getTopContents($userId, $limitContentRelated) {
        $result = Content::where('public', 1)
            ->orWhere(function ($query) use ($userId) {
                $query->where('public', 0)->where('user_created', $userId);
            })
            ->orderBy('updated_at', 'DESC')->limit($limitContentRelated)->get();
        return $result;
    }
}
