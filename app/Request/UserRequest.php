<?php

declare(strict_types=1);
/**
 * This file is part of Nursery2.
 * @author    denglei@4587@163.com
 */
namespace App\Request;

use App\Controller\SmsController;
use App\Exception\BusinessException;
use App\Model\User;
use App\Service\SmsService;
use App\Service\WxService;
use App\Utils\WeiXin\errorCode;
use App\Utils\WeiXin\wxBizDataCrypt;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;
use League\Flysystem\Filesystem;

class UserRequest extends FormRequest
{
    public const BIND_TYPE_WX = 'wx';

    public const BIND_TYPE_CUSTOM = 'custom';

    public const SCENE_BIND_PHONE = 'bindPhone';

    public const SCENE_WX_LOGIN = 'wxLogin';

    public const SCENE_BIND_USERINFO = 'bindUserinfo';

    public array $scenes = [
        'bindPhone' => ['encryptedData', 'iv', 'smsCode', 'bindType', 'phoneNumber', 'code'],
        'bindUserinfo' => ['nickname', 'avatarUrl'],
    ];

    #[Inject]
    protected Cache $cache;

    #[Inject]
    protected Filesystem $filesystem;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->getRequest()->getAttribute('userId');
        return [
            'bindType' => ['required', Rule::in([self::BIND_TYPE_WX, self::BIND_TYPE_CUSTOM])],
            'encryptedData' => ['requiredIf:bindType,wx'],
            'iv' => ['requiredIf:bindType,wx'],
            'code' => ['requiredIf:bindType,wx'],
            'phoneNumber' => ['requiredIf:bindType,custom', 'regex:/^1[3456789]\d{9}$/'],
            'smsCode' => ['requiredIf:bindType,custom', 'digits:6', 'bail', function ($attr, $value, $fail) {
                /** @var SmsService $smsService */
                $smsService = $this->container->get(SmsService::class);
                $checked = $smsService->checkCode(
                    $this->input('phoneNumber'),
                    $value,
                    SmsController::TYPE_BIND_PHONE
                );
                if (! $checked) {
                    $fail('验证码错误');
                }
            }],
            'nickname' => ['required', 'max:20', function ($attr, $value, $fail) use ($userId) {
                if (User::findFromCache($userId)->member_status == User::GUEST) {
                    $fail('请先登录');
                }
            }],
            'avatarUrl' => ['required', function ($attr, $value, $fail) {
                $exist = $this->filesystem->fileExists($value);
                $this->filesystem->fileExists($value);
                if (! $exist) {
                    $fail('头像未上传');
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'nickname.required' => '请填写昵称',
            'avatarUrl.required' => '请上传头像',
            'smsCode.requiredIf' => '请填写验证码',
            'smsCode.digits' => '验证码格式不正确',
        ];
    }

    /**
     * 绑定手机号.
     */
    public function bindPhone()
    {
        $data = $this->validated();
        $user = User::findFromCache($this->getRequest()->getAttribute('userId'));
        $bindPhone = $user->phone;
        if ($data['bindType'] == self::BIND_TYPE_WX) {
            $appId = env('APP_ID');
            $session_key = $this->cache->get('session_key:' . $user->id);
            if (empty($session_key)) {
                // 通过access_token获取手机号
                $wxService = $this->container->get(WxService::class);
                $phone = $wxService->getPhoneNumber($data['code']);
                if (! $phone) {
                    throw new BusinessException(4022, 'session_key过期');
                }
                $bindPhone = $phone;
            } else {
                $wx = new wxBizDataCrypt($appId, $session_key);
                $returnCode = $wx->decryptData($data['encryptedData'], $data['iv'], $result);
                $result = $result ?: [];
                $result = json_decode($result, true);
                if ($result && $returnCode == errorCode::$OK && key_exists('purePhoneNumber', $result)) {
                    $bindPhone = $result['purePhoneNumber'];
                }
            }
        } elseif ($data['bindType'] == self::BIND_TYPE_CUSTOM) {
            $bindPhone = $data['phoneNumber'];
        }
        if ($user->member_status == User::GUEST) {
            $user->member_status = User::MEMBER;
        }
        $user->phone = $bindPhone;
        $ok = $user->save();
        if ($ok) {
            // 刷新redis缓存
            User::findFromCache($user->id);
            return [
                'user_id' => $user->id,
                'username' => $user->name,
                'member_status' => $user->member_status,
                'avatar_url' => $user->full_avatar,
            ];
        }
        throw new BusinessException(500, '绑定手机号失败');
    }

    /**
     * 更新个人信息.
     */
    public function bindUserinfo()
    {
        $data = $this->validated();
        /** @var User $user */
        $user = User::findFromCache($this->getRequest()->getAttribute('userId'));
        $user->name = $data['nickname'];
        $user->avatar = $data['avatarUrl'];
        $user->member_status = User::VIP;
        if ($user->save()) {
            return [
                'user_id' => $user->id,
                'username' => $user->name,
                'member_status' => $user->member_status,
                'avatar_url' => $user->full_avatar,
            ];
        }
        throw new BusinessException(500, '更新个人信息失败');
    }
}
