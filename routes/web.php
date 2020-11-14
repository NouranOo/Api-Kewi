    <?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */
Route::get('/updateapp', function () {
    exec('composer dump-autoload');
    echo 'dump-autoload complete';
});

$router->get('/', function () use ($router) {
    return 'Hello in kewi Apies';
});

/**
 * UserAuth
 */

$router->group(['prefix' => 'Api/User', 'middleware' => ['cors2', 'cors']], function () use ($router) {
    $router->post('/SignUp', 'UserController@SignUp');
    $router->post('/SignIn', 'UserController@SignIn');
    $router->post('/verifyEmail','UserController@verifyEmail');
    $router->post('/LogOut', 'UserController@LogOut');
    $router->post('/ResendVerifyCode' , 'UserController@ResendVerifyCode');
    $router->post('/ForgetPasswordSendRecoveryCode', 'UserController@ForgetPasswordSendRecoveryCode');
    $router->post('/CheckRecoveryCode' , 'UserController@CheckRecoveryCode');
    $router->post('/SetNewPassword' , 'UserController@SetNewPassword');

    $router->post('/loginFacebook' , 'UserController@loginFacebook');
    $router->post('/loginGoogle' , 'UserController@loginGoogle'); 



});
$router->group(['prefix' => 'Api/User', 'middleware' => ['cors2', 'cors', 'UserAuth']], function () use ($router) {
    $router->post('AddPost', 'UserController@AddPost');
    $router->post('PostComment', 'UserController@PostComment');
    $router->post('PostReplay', 'UserController@PostReplay');
    $router->post('GetPosts', 'UserController@GetPosts');
    $router->post('GetPostById', 'UserController@GetPostById');
    $router->post('GetMyPosts', 'UserController@GetMyPosts');
    $router->post('ShowUserProfileById', 'UserController@ShowUserProfileById');
    $router->post('MyNotficationCount', 'UserController@myNotficationCount');
    $router->post('GetAllUsers', 'UserController@GetAllUsers');
    $router->post('LikeUserProfile', 'UserController@LikeUserProfile');
    $router->post('SendMessage', 'UserController@SendMessage');
    $router->post('GetMyConversion', 'UserController@GetAllMessagesByUserId');
    $router->post('GetMyNotfication', 'UserController@GetMyNotfication');
    $router->post('ShowMessageById', 'UserController@ShowMessageById');
    $router->post('UpdateProfileDescription', 'UserController@UpdateProfileDescription');
    $router->post('UploadProfileImage', 'UserController@UploadProfileImage');
    $router->post('MakeUserReport', 'UserController@MakeUserReport');
    $router->post('UpdateUserInformation', 'UserController@UpdateUserInformation');
    $router->post('ShowMyInformation', 'UserController@ShowMyInformation');
    $router->post('UnLikeUserProfile', 'UserController@UnLikeUserProfile');
    $router->post('ShowUserPosts', 'UserController@ShowUserPosts');
    $router->post('DeletePost', 'UserController@DeletePost');
    $router->post('DeleteComment', 'UserController@DeletComment');
    $router->post('DeletReplay', 'UserController@DeletReplay');
    $router->post('DeleteMyAccount', 'UserController@DeleteMyAccount');
    $router->post('MakePostReport', 'UserController@MakePostReport');
    $router->post('MakeCommentReport', 'UserController@MakeCommentReport');
    $router->post('MakeReplyReport', 'UserController@MakeReplyReport');
    $router->post('ChangeNotficationStatus', 'UserController@ChangeNotficationStatus');
    $router->post('readNotfication', 'UserController@readNotfication');
    $router->post('updatefcm', 'UserController@updateFcm');
    $router->post('Follow', 'UserController@Follow');
    $router->post('UnFollow', 'UserController@UnFollow');
    $router->post('Myfollowers', 'UserController@Myfollowers');
    $router->post('Following', 'UserController@Following');
    $router->post('Userfollowers', 'UserController@Userfollowers');
    $router->post('UserFollowing', 'UserController@UserFollowing');
    $router->post('FollowingPosts', 'UserController@FollowingPosts');
    $router->post('LikePost', 'UserController@LikeAPost');
    $router->post('UnLikePost', 'UserController@UnLikeAPost');
    $router->post('SavePost', 'UserController@SavePost');
    $router->post('MySavedPost', 'UserController@MySavedPost');
    $router->post('AddStroy', 'UserController@AddStroy');
    $router->post('Story', 'UserController@Story');
    $router->post('UserStory', 'UserController@UserStory');
    $router->post('showStoryByid', 'UserController@showStoryByid');
    $router->post('LikeStory', 'UserController@LikeStory');
    $router->post('UnLikeStory', 'UserController@UnLikeStory');
    $router->post('searchFriends', 'UserController@searchFriends');
    $router->post('HomePage', 'UserController@HomePage');
    $router->post('News', 'UserController@News');
    $router->post('SeeStory', 'UserController@SeeStory');
    $router->post('GetAllConversions', 'UserController@GetAllConversions');
    $router->post('GetFullConversions', 'UserController@GetFullConversions');
    $router->post('Trends', 'UserController@Trends');
    $router->post('GetMyAnswers', 'UserController@GetMyAnswers');
        $router->post('ShowUserAnswers', 'UserController@ShowUserAnswers');

    
    $router->post('Likecomment', 'UserController@LikeAcomment');
    $router->post('LikeReplay', 'UserController@LikeAReplay');
    $router->post('unLikeComment', 'UserController@unLikecomment');
    $router->post('unLikeReplay', 'UserController@unLikeReplay');
    $router->post('BlockUser', 'UserController@BlockUser');
    $router->post('getMyBlocked', 'UserController@getMyBlocked');
    $router->post('AddInterest', 'UserController@AddInterest');
    $router->post('DeleteANotification', 'UserController@DeleteANotification');
    $router->post('ChangePassword', 'UserController@ChangePassword');
    $router->post('GetIntersts', 'UserController@GetIntersts');
    $router->post('ChangeProfilePicture', 'UserController@ChangeProfilePicture');
    $router->post('ChangeBackgroundPic', 'UserController@ChangeBackgroundPic');
    $router->post('UpdateStatus', 'UserController@UpdateStatus');
    $router->post('EditProfile', 'UserController@EditProfile');
    $router->post('GetMyStudio', 'UserController@GetMyStudio');
    $router->post('GetUserStudio', 'UserController@GetUserStudio');
    $router->post('GetMyLikes', 'UserController@GetMyLikes');
    $router->post('GetUserLikes', 'UserController@GetUserLikes');
    $router->post('FindFriend', 'UserController@FindFriend');
    $router->post('MyLikeActivity', 'UserController@LikeActivity');
    $router->post('UserLikeActivity', 'UserController@UserLikeActivity');
    $router->post('Search', 'UserController@Search');
    $router->post('UpdatePersonalStatus', 'UserController@UpdatePersonalStatus');

    $router->post('getCommentsOfPost', 'UserController@getCommentsOfPost');
    $router->post('unBlockUser', 'UserController@unBlockUser');
    $router->post('DeleteAll', 'UserController@DeleteAll');
    $router->post('ReadAll', 'UserController@ReadAll');
    $router->post('WhoLikedQuestion', 'UserController@WhoLikedQuestion');
    $router->post('WhoLikedReplay', 'UserController@WhoLikedReplay');
    $router->post('WhoLikedComment', 'UserController@WhoLikedComment');
    $router->post('SharedPost', 'UserController@SharedPost');
    $router->post('getSharedPost', 'UserController@getSharedPost');
    $router->post('SharedComment', 'UserController@SharedComment');
    $router->post('getSharedComment', 'UserController@getSharedComment');
    $router->post('ChangeQuestionNotficationStatus', 'UserController@ChangeQuestionNotficationStatus');












});
