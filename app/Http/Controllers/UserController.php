<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Helpers\GeneralHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\UserInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use validator;

class UserController extends Controller
{

    public $user;
    public $apiResponse;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserInterface $user, ApiResponse $apiResponse)
    {
        $this->user = $user;
        $this->apiResponse = $apiResponse;
    }

    /*
     * Auth section
     */
    public function SignUp(Request $request)
    {
        $rules = [
            'Email' => 'required|unique:users',
            'UserName' => 'required',
            'Password' => 'required',
            'ApiKey' => 'required',
            'Token' => '',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }

        $data = $request->all();
        $result = $this->user->SignUp($data);
        return $result->send();
    }

    public function SignIn(Request $request)
    {
        $rules = [
            'Email' => 'required',
            'Password' => 'required',
            'ApiKey' => 'required',
            'Token' => '',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $data = $request->all();
        $result = $this->user->SignIn($request->all());
        return $result->send();

    }
    public function verifyEmail(Request $request)
    {
        $rules = [

            'Email' => 'required',
            'VerifyCode' => 'required',
            'ApiKey' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->verifyEmail($request->all());
        return $result->send();

    }
    public function LogOut(Request $request)
    {
        $rules = [

            'ApiKey' => 'required',
            'ApiToken' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->LogOut($request->all());
        return $result->send();

    }
    public function loginFacebook(Request $request) //54

    {
        $rules = [
            'FacbookId' => 'required',

        ];
        $fbId = User::where('FacbookId', $request->FacbookId)->first();

        if (!is_null($fbId)) {

            $rules = [

                'ApiKey' => 'required',
                'Token' => '',

            ];

        } else {
            $rules = [
                // 'FacbookId' =>'required',
                'ApiKey' => 'required',
                'Token' => 'required',
                'Fname' => ' ',
                'Lname' => ' ',
                'Location' => '',
                'BirthDay' => '',
                'Phone' => '',
                'CountryCode' => '',
                'Photo' => '',
                'HostPost' => '',
                'Email' => '',
                'UserName' => '',
                'Late' => '',
                'Long' => '',
            ];

        }

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized! Plz Enter Your Data")->send();
            // $result = $this->user->loginFacebook($data);
        }

        // $data = $request->except('Photo');

        // if ($request->hasFile('Photo')) {

        //     $file = $request->file("Photo");
        //     $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
        //     $path = 'ProjectFiles/UserPhotos';
        //     $file->move($path, $filename);
        //     $data['Photo'] = $path . '/' . $filename;
        // }
        $data = $request->all();
        $result = $this->user->loginFacebook($data);
        return $result->send();
    }

    public function ResendVerifyCode(Request $request)
    {
        $rules = [
            'Email' => 'required',
            'ApiKey' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->ResendVerifyCode($request->all());
        return $result->send();
    }
    public function ForgetPasswordSendRecoveryCode(Request $request)
    {
        $rules = [
            'Email' => 'required',

            'ApiKey' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->ForgetPasswordSendRecoveryCode($request->all());
        return $result->send();

    }
    public function CheckRecoveryCode(Request $request)
    {
        $rules = [
            'Email' => 'required',
            'RecoveryCode' => 'required',

            'ApiKey' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }

        $data = $request->all();
        $result = $this->user->CheckRecoveryCode($data);
        return $result->send();
    }
    public function SetNewPassword(Request $request)
    {
        $rules = [
            'Email' => 'required',
            'NewPassword' => 'required',
            'RecoveryCode' => 'required',
            'ApiKey' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }

        $data = $request->all();
        $result = $this->user->SetNewPassword($data);
        return $result->send();
    }
    public function ResendCode(Request $request) //35@@

    {
        $rules = [
            'Email' => 'required|Email',
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->ResendCode($request->all());
        return $result->send();
    }

    /**
     * Community Section Add(Posts,comments,replies,likes)
     */
    public function AddPost(Request $request) //4

    {
        $rules = [
            'Post' => 'required|max:500',
            'Video' =>'',
            'Photo' =>'',
            'IsAnonymous' => '',
            'privacy' =>'',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $data = $request->except('Photo');

        if ($request->hasFile('Photo')) {

            $file = $request->file("Photo");
            $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/QuestionsPhoto';
            $file->move($path, $filename);
            $data['Photo'] = $path . '/' . $filename;
        }
        if ($request->hasFile('Video')) {

            $file = $request->file("Video");
            $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/QuestionsPhoto';
            $file->move($path, $filename);
            $data['Video'] = $path . '/' . $filename;
        }
        $result = $this->user->AddPost($data);
        return $result->send();
    }

    public function PostComment(Request $request) //5

    {
        $rules = [
            'Post_id' => 'required',
            'Comment' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }

        $result = $this->user->PostComment($request->all());
        return $result->send();
    }
    public function PostReplay(Request $request) //6

    {
        $rules = [
            'Comment_id' => 'required',
            'Replay' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }

        $result = $this->user->PostReplay($request->all());
        return $result->send();
    }

    public function LikeAPost(Request $request)
    {

        $result = $this->user->LikeAPost($request->all());
        return $result->send();
    }
    public function UnLikeAPost(Request $request)
    {
        $result = $this->user->UnLikeAPost($request->all());
        return $result->send();
    }

    public function FollowingPosts(Request $request)
    {

        $result = $this->user->FollowingPosts($request->all());
        return $result->send();
    }

    public function ShowUserPosts(Request $request)
    {
        $result = $this->user->ShowUserPosts($request->all(),$request);
        return $result->send();
    }

    public function SavePost(Request $request)
    {
        $result = $this->user->SavePost($request->all());
        return $result->send();
    }

    /**
     * Display Community
     */
    public function GetPosts(Request $request)
    {

        $result = $this->user->GetPosts($request->all(),$request);
        return $result->send();

    }
    public function GetPostById(Request $request)
    {

        $rules = [

            'id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->GetPostById($request->all());
        return $result->send();
    }
    public function GetMyPosts(Request $request) //10

    {

        $result = $this->user->GetMyPosts($request->all(),$request);
        return $result->send();

    }
    public function MySavedPost(Request $request)
    {
        $result = $this->user->MySavedPost($request->all());
        return $result->send();
    }
    /**
     * Delete Community
     */
    public function DeletePost(Request $request)
    {

        $rules = [
            'Post_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->DeletePost($request->all());
        return $result->send();
    }

    public function DeletComment(Request $request) //57@@

    {
        $rules = [
            // 'User_id' => '',
            'Comment_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->DeletComment($request->all());
        return $result->send();

    }
    public function DeletReplay(Request $request) //58@@

    { $rules = [
        // 'User_id' => '',
        'Replay_id' => 'required',

    ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->DeletReplay($request->all());
        return $result->send();

    }
    /**
     * profile (User)
     */
    public function ShowUserProfileById(Request $request) //11

    {
        $rules = [
            'User_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->ShowUserProfileById($request->all());
        return $result->send();
    }

    public function GetAllUsers(Request $request) //13

    {
        $result = $this->user->GetAllUsers($request->all());
        return $result->send();

    }
    public function UpdateUserInformation(Request $request) //44@@

    {

        $user = GeneralHelper::getcurrentUser();
        $rules = [
            // 'Email' => 'unique:users,Email,'.$user->id,
            // 'Phone' => 'numeric|unique:users,Phone,'.$user->id,
            'Email' => '',
            'Phone' => '',
            'UserName' => 'max:15|min:3|unique:users,UserName,' . $user->id,
            'Password' => 'between:6,20',
            'CountryCode' => '',
            'Fname' => '',
            'Lname' => '',
            'BirthDay' => '',
            'Location' => '',
            'FacbookId' => '',
            'HostPost' => '',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

        }

        $data = $request->except(['Photo', 'Password']);

        if ($request->hasFile('Photo')) {

            $file = $request->file("Photo");
            $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/UserPhotos';
            $file->move($path, $filename);
            $data['Photo'] = $path . '/' . $filename;
        }if ($request->Password) {
            $data['Password'] = Hash::make($request->Password);
        }

        $result = $this->user->UpdateUserInformation($data);
        return $result->send();
    }

    public function ShowMyInformation(Request $request) //45@@

    {

        $result = $this->user->ShowMyInformation($request->all());
        return $result->send();

    }

    public function UnLikeUserProfile(Request $request) //46@@

    {
        $rules = [
            'User_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->UnLikeUserProfile($request->all());
        return $result->send();

    }

    public function LikeUserProfile(Request $request) //14

    {
        $rules = [
            'User_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->LikeUserProfile($request->all());
        return $result->send();

    }
    public function UpdateProfileDescription(Request $request) //38@@

    {
        $rules = [

            'Describition' => '',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($request->Describition) {
            $data['Describition'] = $request->Describition;
        } else {
            $data['Photo'] = "NULL";
        }

        $result = $this->user->UpdateProfileDescription($request->all());
        return $result->send();

    }

    public function UploadProfileImage(Request $request) //39@@

    {

        $rules = [
            'Photo' => 'image|max:5000',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $data = $request->except('Photo');
        if ($request->hasFile('Photo')) {

            $file = $request->file("Photo");
            $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/UserPhotos';
            $file->move($path, $filename);
            $data['Photo'] = $path . '/' . $filename;
        } else {
            $data['Photo'] = "NULL";
        }

        $result = $this->user->UploadProfileImage($data);
        return $result->send();

    }

    public function MakeUserReport(Request $request) //40@@

    {

        $rules = [
            'Reported_id' => 'required',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->MakeUserReport($request->all());
        return $result->send();

    }

    /*
     * Message Chat
     **/
    public function SendMessage(Request $request) //18

    {

        $rules = [
            'Message' => 'required',
            'Message_to' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->SendMessage($request->all());
        return $result->send();
    }

    //user ID => who i sent to target
    public function GetAllMessagesByUserId(Request $request) //19

    {
        $rules = [

            'To' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }
        $result = $this->user->GetAllMessagesByUserId($request->all());
        return $result->send();
    }

    public function ShowMessageById(Request $request) //21

    {

        $rules = [

            'id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->ShowMessageById($request->all());
        return $result->send();
    }
    public function GetFullConversions(Request $request)
    {
        $result = $this->user->GetFullConversions($request->all());
        return $result->send();

    }
/**
 * Notfication
 */
    public function GetMyNotfication(Request $request) //20

    {
        $result = $this->user->GetMyNotfication($request->all());
        return $result->send();
    }
    public function readNotfication(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->readNotfication($request->all());
        return $result->send();

    }
    public function MyNotficationCount(Request $request)
    {

        $result = $this->user->MyNotficationCount($request->all());
        return $result->send();
    }

    public function updateFcm(Request $request)
    {

        $result = $this->user->updateFcm($request->all());
        return $result->send();
    }
    //disable or enable  push notifaction
    public function ChangeNotficationStatus(Request $request) //34@@

    {

        $rules = [
            'status' => 'required|in:1,2',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->ChangeNotficationStatus($request->all());
        return $result->send();

    }
/**
 *
 *  Requests (followers-follwing)
 **/

    public function DeleteMyAccount(Request $request) //59@@

    {

        $result = $this->user->DeleteMyAccount($request->all());
        return $result->send();

    }
    public function DeleteMyFriend(Request $request) //60@@

    {

        $rules = [

            'Friend_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->DeleteMyFriend($request->all());
        return $result->send();

    }

    public function MakeReplyReport(Request $request)
    {

        $rules = [

            'Reply_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->MakeReplyReport($request->all());
        return $result->send();

    }

    /**
     * follow
     * unfollow
     * story
     * flashes
     * stories
     */
    public function Follow(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->Follow($request->all());
        return $result->send();
    }
    public function UnFollow(Request $request)
    {
        $rules = [
            'user_id' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce
        }
        $result = $this->user->UnFollow($request->all());
        return $result->send();
    }
    public function MyFollowers(Request $request)
    {

        $result = $this->user->MyFollowers($request->all(),$request);
        return $result->send();
    }
    public function Following(Request $request)
    {

        $result = $this->user->Following($request->all(),$request);
        return $result->send();
    }
        public function UserFollowers(Request $request)
    {

        $result = $this->user->UserFollowers($request->all(),$request);
        return $result->send();
    }
    public function UserFollowing(Request $request)
    {

        $result = $this->user->UserFollowing($request->all(),$request);
        return $result->send();
    }

    public function AddStroy(Request $request)
    {
        $data = $request->except('photo');

        if ($request->hasFile('photo')) {

            $file = $request->file("photo");
            $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
            $path = 'ProjectFiles/stories';
            $file->move($path, $filename);
            $data['photo'] = $path . '/' . $filename;
        }

        $result = $this->user->AddStroy($data);
        return $result->send();
    }
    public function showStoryByid(Request $request)
    {

        $result = $this->user->showStoryByid($request->all());
        return $result->send();
    }

    public function UserStory(Request $request)
    {
        $result = $this->user->UserStory($request->all());
        return $result->send();
    }

    public function Trends(Request $request)
    {
        $result = $this->user->Trends($request->all(),$request);
        return $result->send();

    }
    public function News(Request $request)
    {
        $result = $this->user->News($request->all(),$request);
        return $result->send();

    }
    public function HomePage(Request $request)
    {
        $result = $this->user->HomePage($request->all(),$request);
        return $result->send();

    }
    public function searchFriends(Request $request)
    {
        $result = $this->user->searchFriends($request->all());
        return $result->send();
    }

    public function LikeAcomment(Request $request)
    {
        $result = $this->user->LikeAcomment($request->all());
        return $result->send();
    }
    public function LikeAReplay(Request $request)
    {
        $result = $this->user->LikeAReplay($request->all());
        return $result->send();
    }
    public function unLikecomment(Request $request)
    {
        $result = $this->user->unLikecomment($request->all());
        return $result->send();
    }
    public function unLikeReplay(Request $request)
    {
        $result = $this->user->unLikeReplay($request->all());
        return $result->send();
    }

    public function BlockUser(Request $request)
    {
        $result = $this->user->BlockUser($request->all());
        return $result->send();
    }
    public function getMyBlocked(Request $request)
    {
        $result = $this->user->getMyBlocked($request->all());
        return $result->send();
    }
    public function AddInterest(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'Interest_id' => 'required',
            'ApiKey' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->AddInterest($request->all());
        return $result->send();
    }
    public function DeleteANotification(Request $request)
    {
        $result = $this->user->DeleteANotification($request->all());
        return $result->send();
    }
    public function ChangePassword(Request $request)
    {
        $rules = [
            'CurrentPass' => 'required',
            'NewPass' => 'required',
            'ApiKey' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }

        $result = $this->user->ChangePassword($request->all());
        return $result->send();
    }
    public function GetIntersts(Request $request)
    {
        $rules = [

            'ApiKey' => 'required',
            'ApiToken' =>'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }

        $result = $this->user->GetIntersts($request->all());
        return $result->send();
    }
    public function ChangeProfilePicture(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'Photo' =>'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
            $data = $request->except('Photo');
            if ($request->hasFile('Photo')) {

                    $file = $request->file("Photo");
                    $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
                    $path = 'ProjectFiles/UserPhotos';
                    $file->move($path, $filename);
                    $data['Photo'] = $path . '/' . $filename;
            }else
                    $data['Photo']="NULL";


        $result = $this->user->ChangeProfilePicture($data);
        return $result->send();

    }
    public function ChangeBackgroundPic(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'BackgroundPhoto' =>'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
            $data = $request->except('BackgroundPhoto');
            if ($request->hasFile('BackgroundPhoto')) {

                    $file = $request->file("BackgroundPhoto");
                    $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
                    $path = 'ProjectFiles/UserPhotos';
                    $file->move($path, $filename);
                    $data['BackgroundPhoto'] = $path . '/' . $filename;
                    // dd("dd");
            }else
                    $data['BackgroundPhoto']="NULL";


        $result = $this->user->ChangeBackgroundPic($data);
        return $result->send();


    }
    public function UpdateStatus(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'UserName' =>'',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->UpdateStatus($request->all());
        return $result->send();
    }
    public function EditProfile(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'Name' =>'',
            'UserName' =>'',
            'Gendre' =>'',
            'Location'=>'',



        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->EditProfile($request->all());
        return $result->send();

    }
    public function GetMyStudio(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->GetMyStudio($request->all());
        return $result->send();


    }
    public function GetUserStudio(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'User_id' => 'required',


        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->GetUserStudio($request->all());
        return $result->send();

    }
    public function GetMyLikes(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',



        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->GetMyLikes($request->all());
        return $result->send();

    }
    public function GetUserLikes(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'User_id' => 'required',


        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->GetUserLikes($request->all());
        return $result->send();
    }

    public function GetMyAnswers(Request $request){
        $result = $this->user->GetMyAnswers($request->all(),$request);
        return $result->send();
    }
       public function ShowUserAnswers(Request $request){
        $result = $this->user->ShowUserAnswers($request->all(),$request);
        return $result->send();
    }
    public function FindFriend(Request $request){
        $result = $this->user->FindFriend($request->all());
        return $result->send();

    }
       public function WhoLikedQuestion(Request $request){
        $result = $this->user->WhoLikedQuestion($request->all());
        return $result->send();

    }
       public function WhoLikedReplay(Request $request){
        $result = $this->user->WhoLikedReplay($request->all());
        return $result->send();

    }
       public function WhoLikedComment(Request $request){
        $result = $this->user->WhoLikedComment($request->all());
        return $result->send();

    }
    public function LikeActivity(Request $request){
        $result = $this->user->LikeActivity($request->all());
        return $result->send();

    }
    public function UserLikeActivity(Request $request){
        $result = $this->user->UserLikeActivity($request->all());
        return $result->send();

    }
    public function loginGoogle(Request $request)

   {
       $rules = [
           'GoogleId' => 'required',

       ];
       $fbId = User::where('GoogleId', $request->GoogleId)->first();

       if (!is_null($fbId)) {

           $rules = [

               'ApiKey' => 'required',
               'Token' => 'required',

           ];

       } else {
           $rules = [
               // 'FacbookId' =>'required',
               'ApiKey' => 'required',
               'Token' => 'required',
               // 'UserName'=>'',
               'Fname' => ' ',
               'Lname' => ' ',
               'Location' => '',
               'BirthDay' => '',
               'Phone' => '',
               'CountryCode' => '',
               'Photo' => '',
               'HostPost' => '',
               'Email' => '',
               'UserName' => '',
               'Late' => '',
               'Long' => '',
           ];

       }

       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send(); //new way to send responce

       }
       $api_key = env('APP_KEY');
       if ($api_key != $request->ApiKey) {
           return $this->apiResponse->setError("Unauthorized! Plz Enter Your Data")->send();
           // $result = $this->user->loginFacebook($data);
       }

       // $data = $request->except('Photo');

       // if ($request->hasFile('Photo')) {

       //     $file = $request->file("Photo");
       //     $filename = str_random(6) . '_' . time() . '_' . $file->getClientOriginalName();
       //     $path = 'ProjectFiles/UserPhotos';
       //     $file->move($path, $filename);
       //     $data['Photo'] = $path . '/' . $filename;
       // }
       $data = $request->all();
       $result = $this->user->loginGoogle($data);
       return $result->send();
   }
    public function Search(Request $request)
   {
       $rules = [
           'ApiToken' => 'required',
           'ApiKey' => 'required',
           'key' => 'required',

           
       ];

       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send();

       }
       $api_key = env('APP_KEY');
       if ($api_key != $request->ApiKey) {
           return $this->apiResponse->setError("Unauthorized!")->send();
       }
       $result = $this->user->Search($request->all(),$request);
       return $result->send();
   }
    public function UpdatePersonalStatus(Request $request)
    {
        $rules = [
            'ApiToken' => 'required',
            'ApiKey' => 'required',
            'Personal_Status' =>'',

        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();

        }
        $api_key = env('APP_KEY');
        if ($api_key != $request->ApiKey) {
            return $this->apiResponse->setError("Unauthorized!")->send();
        }
        $result = $this->user->UpdatePersonalStatus($request->all());
        return $result->send();
    }
     public function getCommentsOfPost(Request $request)
   {
       $rules = [
           'ApiToken' => 'required',
           'ApiKey' => 'required',
           'Comment_id' => 'required',

           
       ];

       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send();

       }
       $api_key = env('APP_KEY');
       if ($api_key != $request->ApiKey) {
           return $this->apiResponse->setError("Unauthorized!")->send();
       }
       $result = $this->user->getCommentsOfPost($request->all());
       return $result->send();
   }
   public function unBlockUser(Request $request)
   {
       $result = $this->user->unBlockUser($request->all());
       return $result->send();
   }
   public function DeleteAll(Request $request)
   {
       $result = $this->user->DeleteAll($request->all());
       return $result->send();
   }
   public function ReadAll(Request $request)
   {
       $result = $this->user->ReadAll($request->all());
       return $result->send();
   }
   public function MakePostReport(Request $request)
    {

        $rules = [

            'Post_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->MakePostReport($request->all());
        return $result->send();

    }
       public function MakeCommentReport(Request $request)
    {

        $rules = [

            'Comment_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->MakeCommentReport($request->all());
        return $result->send();

    }
    public function SharedPost(Request $request)
    {

        $rules = [

            'Post_id' => 'required',

        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return $this->apiResponse->setError($validation->errors()->first())->send();
        }

        $result = $this->user->SharedPost($request->all());
        return $result->send();

    }
     public function getSharedPost(Request $request)
   {

       $rules = [

           'Post_id' => 'required',

       ];
       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send();
       }

       $result = $this->user->getSharedPost($request->all());
       return $result->send();

   }
   public function SharedComment(Request $request)
   {

       $rules = [

           'Comment_id' => 'required',

       ];
       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send();
       }

       $result = $this->user->SharedComment($request->all());
       return $result->send();

   }
   public function getSharedComment(Request $request)
   {

       $rules = [

           'Comment_id' => 'required',

       ];
       $validation = Validator::make($request->all(), $rules);

       if ($validation->fails()) {
           return $this->apiResponse->setError($validation->errors()->first())->send();
       }

       $result = $this->user->getSharedComment($request->all());
       return $result->send();

   }
   public function ChangeQuestionNotficationStatus(Request $request){
      
       $result = $this->user->ChangeQuestionNotficationStatus($request->all());
       return $result->send();

       
   }


}
