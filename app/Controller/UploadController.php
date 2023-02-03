<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Controller;

use App\Exception\BusinessException;
use App\Middleware\JwtAuthMiddleware;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\Coroutine;
use League\Flysystem\FilesystemException;

#[Controller]
class UploadController extends AbstractController
{
    /**
     *显示图片.
     */
    #[GetMapping(path: '/show')]
    public function show(ResponseInterface $response)
    {
        //        Coroutine::sleep(10);
        return $response->withHeader('Content-Type', 'image/jpeg')->withStatus(200)->withBody(new SwooleFileStream('./public/static/images/123.jpg'));
    }

    #[PostMapping(path: 'upload')]
    #[Middlewares([JwtAuthMiddleware::class])]
    public function uploadImage(RequestInterface $request, \League\Flysystem\Filesystem $filesystem)
    {
        $file = $request->file('file');
        $type = $request->post('type');
        $file->isValid();
        switch ($type) {
            case 'avatar':
                $path = '/nursery/avatar';
                break;
            case 'products':
                $path = '/nursery/products';
                break;
            case 'message':
                $path = '/nursery/message';
                break;
            default:
                $path = '/nursery/products';
        }
        if (empty($file) || ! $file->isValid()) {
            throw new BusinessException(400, '请选择正确的文件！');
        }
        $fileSize = config('upload.image_size', 1024 * 1024 * 4);
        if ($file->getSize() > $fileSize) {
            throw new BusinessException(1000, '文件不能大于！' . $fileSize / 1024 / 1024 . 'MB');
        }
        $imageMimes = explode(',', config('upload.image_mimes') ?? 'jpeg,bmp,png,gif,jpg,mp4');
        if (! in_array(strtolower($file->getExtension()), $imageMimes)) {
            throw new BusinessException(1000, '后缀不允许！');
        }
        # 检测类型
        if (! in_array(strtolower($file->getClientMediaType()), ['image/gif', 'image/jpeg', 'image/jpg', 'image/png', 'image/pjpeg', 'image/x-png', 'video/mp4', 'video/ogg'])) {
            throw new BusinessException(1000, '不允许上传此文件！');
        }
        $file_name = $path . '/' . date('Ym') . '/' . date('d') . '/' . uniqid() . '.' . strtolower($file->getExtension());
        try {
            $filesystem->write($file_name, $file->getStream()->getContents());
            return $this->success(['url' => config('file.storage.oss.prefix') . $file_name, 'path' => $file_name]);
        } catch (FilesystemException $exception) {
            throw new BusinessException(500, '上传失败');
        }
    }
}
