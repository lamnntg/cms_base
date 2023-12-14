<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class NewsService implements NewsServiceInterface
{
    public function store(array $data)
    {
        $nextAutoIncrement = News::next();
        $slug = \Str::slug($data['title']) . '-' . $nextAutoIncrement;
        $newsData = News::where('slug', $slug)->withTrashed()->exists();

        $dataSave = [
            'user_id' => empty($data['user_id']) ? 0 : $data['user_id'],
            'title' => $data['title'],
            'content' => empty($data['content']) ? '' : $data['content'],
            'slug' => $slug,
            'images' => !empty($data['images']) ? [$data['images']] : [],
            'status' => News::STATUS_ACCEPTED
        ];

        if (!empty($dataSave['images'])) {
            foreach ($dataSave['images'] as $dataImage) {
                // Lưu ảnh
                if (!empty($dataImage)) {
                    $profile = $dataImage;
                    $filename = Str::uuid(time()) . '_' . $profile->getClientOriginalName();

                    $uploadPath = public_path('/img/news');

                    // Kiểm tra xem thư mục đã tồn tại chưa, nếu không thì tạo mới
                    if (!File::exists($uploadPath)) {
                        File::makeDirectory($uploadPath, 0777, true, true);
                    }

                    if (move_uploaded_file($profile, $uploadPath . '/' . $filename)) {
                        $dataSave['images'][] = $filename;
                    } else {
                        return [Response::HTTP_INTERNAL_SERVER_ERROR, ['message' => 'Upload file local fail!']];
                    }
                }
            }
        }

        try {
            if (!$newsData) {
                News::create($dataSave);
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This title exists.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }

        return [Response::HTTP_OK, []];
    }

    public function hardDelete(int $id)
    {
        try {
            $news = News::withTrashed()->find($id);

            if ($news) {
                $news->forceDelete();
                return [Response::HTTP_OK, ['message' => 'This record has force deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }

    public function softDelete(int $id)
    {
        try {
            $news = News::find($id);

            if ($news) {
                $news->delete();
                return [Response::HTTP_OK, ['message' => 'This record has soft deleted.']];
            } else {
                return [Response::HTTP_BAD_REQUEST, ['message' => 'This record not found.']];
            }
        } catch (\Exception $e) {
            return [Response::HTTP_INTERNAL_SERVER_ERROR, $e];
        }
    }
}
