<?php

namespace App\Interfaces;

interface UserInterface
{

    public function SignUp($data);
    public function SignIn($data);
    public function verifyEmail($data);
    public function LogOut($data);
    public function ResendVerifyCode($data);
    public function ForgetPasswordSendRecoveryCode($data);
    public function CheckRecoveryCode($data);
    public function SetNewPassword($data);
    public function AddPost($data);
    public function PostComment($data);
    public function PostReplay($data);
    public function GetPosts($data,$request);
    public function GetPostById($data);
    public function GetMyPosts($data,$request);
    public function ShowUserProfileById($data);
    public function GetAllUsers($data);
    public function LikeUserProfile($data);
    public function SendMessage($data);
    public function GetAllMessagesByUserId($data);
    public function GetMyNotfication($data);
    public function ShowMessageById($data);
    public function UpdateProfileDescription($data);
    public function UploadProfileImage($data);
    public function MakeUserReport($data);
    public function UpdateUserInformation($data);
    public function ShowMyInformation($data);
    public function UnLikeUserProfile($data);
    public function ShowUserPosts($data,$request);
    public function loginFacebook($data);
    public function DeletePost($data);
    public function DeletComment($data);
    public function DeletReplay($data);
    public function DeleteMyAccount($data);
    public function MakePostReport($data);
    public function MakeCommentReport($data);
    public function MakeReplyReport($data);
    public function ChangeNotficationStatus($data);
    public function readNotfication($data);
    public function MyNotficationCount($data);
    public function updateFcm($data);
    public function Follow($data);
    public function UnFollow($data);
    public function MyFollowers($data,$request);
    public function Following($data,$request);
    public function UserFollowers($data,$request);
    public function UserFollowing($data,$request);
    public function FollowingPosts($data);
    public function LikeAPost($data);
    public function UnLikeAPost($data);
    public function SavePost($data);
    public function MySavedPost($data);
    public function AddStroy($data);
    public function UserStory($data);
    public function GetFullConversions($data);
    public function Trends($data,$request);
    public function News($data,$request);
    public function HomePage($data,$request);
    public function searchFriends($data);
    public function showStoryByid($data);
    public function LikeAcomment($data);
    public function LikeAReplay($data);
    public function unLikecomment($data);
    public function unLikeReplay($data);
    public function BlockUser($data);
    public function getMyBlocked($data);
    public function AddInterest($data);
    public function DeleteANotification($data);
    public function ChangePassword($data);
    public function GetIntersts($data);
    public function ChangeProfilePicture($data);
    public function ChangeBackgroundPic($data);
    public function UpdateStatus($data);
    public function EditProfile($data);
    public function GetMyStudio($data);
    public function GetUserStudio($data);
    public function GetMyLikes($data);
    public function GetMyAnswers($data,$request);
    public function GetUserLikes($data);
    public function FindFriend($data);
    public function LikeActivity($data);
    public function UserLikeActivity($data);
    public function loginGoogle($data);
    public function Search($data,$request);
    public function UpdatePersonalStatus($data);
    public function getCommentsOfPost($data);
    public function unBlockUser($data);
    public function DeleteAll($data);
    public function ReadAll($data);
    public function ShowUserAnswers($data,$request);
    public function WhoLikedQuestion($data);
    public function WhoLikedReplay($data);
    public function WhoLikedComment($data);
    public function SharedPost($data);
    public function getSharedPost($data);
    public function SharedComment($data);
    public function getSharedComment($data);
    public function ChangeQuestionNotficationStatus($data);
    



}
