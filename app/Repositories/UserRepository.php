<?php
namespace App\Repositories;

use App\Helpers\ApiResponse;
use App\helpers\FCMHelper;
use App\Helpers\GeneralHelper;
use App\Interfaces\UserInterface;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\LikeUserProfile;
use App\Models\Message;
use App\Models\Notfication;
use App\Models\Post;
use App\Models\Post_like;
use App\Models\Replay;
use App\Models\Report;
use App\Models\Save;
use App\Models\Story;
use App\Models\User;
use App\Models\Comment_like;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Models\Replay_like;
use App\Models\block;
use App\Models\Interst;
use App\Models\UserInterst;
use App\Models\studio;
use App\Models\LikeActivity;
use App\Models\Shared_Post;
use App\Models\Shared_Comment;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;


class UserRepository implements UserInterface
{

    public $apiResponse;
    public $generalhelper;
    public function __construct(GeneralHelper $generalhelper, ApiResponse $apiResponse)
    {
        $this->generalhelper = $generalhelper;

        $this->apiResponse = $apiResponse;

    }
    /** auth section
     *
     */
    public function SignUp($data)
    {

        try {
            $data['ApiToken'] = base64_encode(str_random(40));
            $data['VerifyCode'] = base64_encode(str_random(6));
            $data['Password'] = app('hash')->make($data['Password']);
            $data['AgreePrivacy'] = 1;

            $user = User::create($data);

        } catch (Exception $ex) {
            return $this->apiResponse->setError("Missing data ", $ex)->setData();
        }

        $verify = GeneralHelper::verifyEmail($user);
        return $this->apiResponse->setSuccess("User created succesfully")->setData($user);

    }

    public function SignIn($data)
    {
        $user = User::where('Email', $data['Email'])->first();

        if ($user) {
            $check = Hash::check($data['Password'], $user->Password);
            if ($check) {
                if ($user->Verified == 1) {
                    try {
                        if(!empty($data['Token'])){
                            $user->Token=$data['Token'];
                            $user->save();
                        }
                        $user->update(['ApiToken' => base64_encode(str_random(40))]);

                        $user->save();
                    } catch (\Illuminate\Database\QueryException $ex) {
                        return $this->apiResponse->setError($ex->getMessage())->setData();
                    }
                    return $this->apiResponse->setSuccess("Login Successfuly")->setData($user);

                } else {
                    return $this->apiResponse->setError("Email Not verfied!")->setVerify("False")->setData();
                }

            } else {
                return $this->apiResponse->setError("Password not Correct!")->setData();
            }

        } else {
            return $this->apiResponse->setError("Your Email not found!")->setData();
        }

    }
    public function verifyEmail($data)
    {
       
        try{
            $user = User::where('Email','like', '%'.$data['Email'].'%')->first();
             if($user){
                
                    if ($user->where('VerifyCode', 'like', '%'.$data['VerifyCode'].'%')->first()) {
                $user = User::find($user->id);
                $user->Verified = 1;
                $user->save();

                return $this->apiResponse->setSuccess("Verified Email Successfully")->setData($user);

            } else {

                return $this->apiResponse->setError(" VerifyCode Not correct ");

            }
             }
                 return $this->apiResponse->setError("Email not correct");

         

        }catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }


    }

    public function LogOut($data)
    {
        $user = User::where('ApiToken', $data['ApiToken'])->first();

        if ($user) {

            try {
                $user->update(['ApiToken' => "NULL"]);
                $user->save();
            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->apiResponse->setError($ex->getMessage())->setData();
            }
            return $this->apiResponse->setSuccess("LogOut Successfuly")->setData();

        } else {
            return $this->apiResponse->setError("UnAuthorized! (invalid ApiToken)")->setData();
        }

    }

    public function ResendVerifyCode($data)
    {

        $user = User::where('Email', $data['Email'])->first();
      

        if ($user) {
            $user->VerifyCode = base64_encode(str_random(6));
            $user->save();
            $verify = GeneralHelper::verifyEmail($user);

            return $this->apiResponse->setSuccess("Email verfication sent successfully");

        } else {
            return $this->apiResponse->setError("Email not found");

        }
    }

    public function ForgetPasswordSendRecoveryCode($data)
    {
        $user = User::where('Email', $data['Email'])->first();
        if ($user and $user->Verified == 0) {

            return $this->apiResponse->setError("Email Not verfied!")->setData();

        }
        if ($user) {
            try {
                $data['RecoveryCode'] = base64_encode(str_random(6));

                $user->RecoveryCode = $data['RecoveryCode'];
                $user->save();

            } catch (\Illuminate\Database\QueryException $ex) {
                return $this->apiResponse->setError($ex->getMessage())->setData();
            }
            $verify = GeneralHelper::RecoveryEmail($user);
            return $this->apiResponse->setSuccess("Check Your Mail For RecoveryCode")->setData($user);
        } else {
            return $this->apiResponse->setError("Your email not found!")->setData();
        }

    }

    public function CheckRecoveryCode($data)
    {

        $user = User::where('Email', $data['Email'])->where('RecoveryCode', $data['RecoveryCode'])->first();
        if ($user) {

            return $this->apiResponse->setSuccess("Correct RecoveryCode")->setVerify("true");

        } else {
            return $this->apiResponse->setError("Check Your Mail or RecoveryCode  ")->setVerify("false");

        }

    }
    public function SetNewPassword($data)
    {

        $user = User::where('Email', $data['Email'])->where('RecoveryCode', $data['RecoveryCode'])->first();
        if ($user) {
            $user = User::find($user->id);
            $data['NewPassword'] = app('hash')->make($data['NewPassword']);
            $user->Password = $data['NewPassword'];
            $user->save();
            return $this->apiResponse->setSuccess("Password Changed Successfuly")->setVerify("true");

        } else {
            return $this->apiResponse->setError("Email not found ")->setVerify("false");

        }

    }

    public function loginFacebook($data)
    {
        $fbId = User::where('FacbookId', $data['FacbookId'])->first();

        if ($fbId) {
            return $this->apiResponse->setSuccess("User Found ")->setData($fbId);
        } else {
            $data['ApiToken'] = base64_encode(str_random(40));
            try {
                $user = User::create($data);
                $user->FacbookId = $data['FacbookId'];

                if ($user->Photo == null) {
                    $user->Photo = "";
                }else{
                    studio::create(['User_id'=>$user->id,'Photo'=>$user->Photo,'Date'=>Carbon::now()]);
                }

                $user->save();
            } catch (Exception $ex) {
                return $this->apiResponse->setError("Missing data ", $ex)->setData();
            }

            return $this->apiResponse->setSuccess("User Created successfuly ")->setData($user);
        }
    }

    /**
     * notfication
     */
    public function GetMyNotfication($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            // $messages = Notfication::where('User_id', $user->id)->get();
            $messages = Notfication::where('User_id', $user->id)->with('userFrom')->orderby('id','desc')->paginate(8);
            $messages->each(function ($item, $key)  {
            $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
            

        });

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Notifications Fetched Successfuly")->setData($messages);

    }
    public function readNotfication($data)
    {
        $not = Notfication::find($data['id']);
        $not->Seen = 1;
        $not->save();return $this->apiResponse->setSuccess("Notfication marked as read")->setData();
    }
     public function DeleteAll($data)
    {
        $not = Notfication::where('User_id',$data['id'])->delete();
        
         return $this->apiResponse->setSuccess("Notfication Deleted Successffully")->setData();
    }
     public function ReadAll($data)
    {
        $not = Notfication::where('User_id',$data['id'])->update(['Seen'=>1]);
        
         return $this->apiResponse->setSuccess(" All Notfication Marked as  Reade Successffully")->setData();
    }
    
    //update  fcm token for notfication
    public function updateFcm($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $user->Token = $data['Token'];
        $user->save();
        return $this->apiResponse->setSuccess("fcm updated successfully")->setData();

    }
    public function ChangeQuestionNotficationStatus($data){
    $user = GeneralHelper::getcurrentUser();
    $newuser=User::where('id',$user->id)->first();
    if($data['status']=="false"){
    $newuser->QuestionNotify=1;
    $newuser->save();
    return $this->apiResponse->setSuccess("question Notfication disabled successfully")->setData();
    }if($data['status']="true")
    {
    $newuser->QuestionNotify=0;
    $newuser->save();
     return $this->apiResponse->setSuccess("question Notfication enabled successfully")->setData();

    }
   
    
    return $this->apiResponse->setSuccess("question doesn't updated ")->setData();
        
    }

    /**
     * disable or enable push notifcation
     *status 2 disabled
     *status 1 enable with @params token
     *  */
    public function ChangeNotficationStatus($data)
    {
        $user = GeneralHelper::getcurrentUser();
        if ($data['status'] == 2) {
            $user->Token = '';
            $user->save();
            return $this->apiResponse->setSuccess("Notfication Disapled")->setData();
        }if ($data['status'] == 1) {
            $user->Token = $data['Token'];
            $user->save();
            return $this->apiResponse->setSuccess("Notfication Enapled")->setData();

        }

    }
    //calculate notfication count

    public function MyNotficationCount($data)
    {

        $user = GeneralHelper::getcurrentUser();
        $count = Notfication::where('User_id', $user->id)->where('Seen', 0)->get();

        $object = new \stdClass();
        $object->count = $count->count();
        return $this->apiResponse->setSuccess("Notfication count")->setData($object);
    }
    /**
     * post community
     */
    public function AddPost($data)

    {
        $user = GeneralHelper::getcurrentUser();
        $data['User_id'] = $user->id;

        try {
            $Post = Post::create($data);
          if($user->QuestionNotify==0){
            //if post is asked for some one
            if($Post->Asked_id!=0){
                     GeneralHelper::SetNotfication('Anonymous'. ' ' . 'Asked You  An Question', $data['Post'], 'Post', $user->id, $Post->Asked_id, $Post->id, "Question",1);
                    //--------------------------FireBaseNotfication------------------------------------------
                    $TargetUser = User::where('id', $Post->Asked_id)->first();
                    $data1 = array('title' => 'kewi', 'body' => 'Anonymous' . ' ' . 'Asked You a Question', 'Key' => 'Notify');
                    $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
                    //---------------------------------------------------------------------
                }else{
 
               
            
                }

             
        }
            //save photo in my studio
            if(!empty($data['Photo']))
            studio::create(['User_id'=>$user->id,'Photo'=>$data['Photo'],'Date'=>Carbon::now()->format('Y-m-d')]);

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Your post published Successfuly")->setData($Post);
    }
    public function PostComment($data)

    {
        $user = GeneralHelper::getcurrentUser();
        $data['User_id'] = $user->id;
        try {
            $Comment = Comment::create($data);
            $PostOwner = Post::find($data['Post_id']);
                      if($user->QuestionNotify==0){

            if ($PostOwner->User_id != $user->id) {
                GeneralHelper::SetNotfication($user->UserName . ' ' . ' Answered on your Question', $data['Comment'], 'Post', $user->id, $PostOwner->User_id, $PostOwner->id, "Comment");
                //--------------------------FireBaseNotfication------------------------------------------
                $targetUser = User::where('id', $PostOwner->User_id)->first();
                $data1 = array('title' => 'Kewi', 'body' => $user->UserName . ' ' . ' Answered On Your Question', 'Key' => 'Notify');
                $res = FCMHelper::sendFCMMessage($data1, $targetUser->Token);
               
                //---------------------------------------------------------------------
            }
                      }

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Comment Posted Successfuly")->setData($Comment);

    }
    public function PostReplay($data)

    {
        $user = GeneralHelper::getcurrentUser();
        $data['User_id'] = $user->id;
        $comment=Comment::find($data['Comment_id'])->first();
        if($comment){
            $m=$comment->Post_id;
        }else{
                    return $this->apiResponse->setSuccess("comment doesn't exist")->setData();

        }
        try {
            $Post = Post::where('id',$m )->first();
            $Replay = Replay::create($data);
                       if($user->QuestionNotify==0){

            // if ($Replay->User_id != $user->id) {
                GeneralHelper::SetNotfication($user->UserName . ' ' . 'Commented On Your Answer', $data['Replay'], 'Post', $user->id, $Replay->User_id, $Post->id, "Replay");
                //--------------------------FireBaseNotfication------------------------------------------
                $TargetUser = User::where('id', $Replay->User_id)->first();
                $data1 = array('title' => 'kewi', 'body' => $user->UserName . ' ' . 'Commented  On Your Answer', 'Key' => 'Notify');
                $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
                //---------------------------------------------------------------------
            // }
                       }
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Replay Posted Successfuly")->setData($Replay);

    }
    public function LikeAPost($data)
    {
        $user = GeneralHelper::getcurrentUser();

        $found = Post_like::where('User_id', $user->id)->where('Post_id', $data['id'])->first();
        if ($found) {
            return $this->apiResponse->setSuccess("  You  Liked  Post Before")->setData();

        }
        $user = GeneralHelper::getcurrentUser();
        $post = Post::where('id', $data['id'])->first();

        $post->Likes = (int) $post->Likes + 1;
        $post->save();
        Post_like::create(['User_id' => $user->id, 'Post_id' => $post->id]);
        //like question
                  if($user->QuestionNotify==0){

        GeneralHelper::SetNotfication($user->UserName . ' ' . 'Liked  Your Question', 'Like', 'Like', $user->id, $post->User_id, $post->id, "Like");
        //--------------------------FireBaseNotfication------------------------------------------
        $TargetUser = User::where('id', $post->User_id)->first();
        $data1 = array('title' => 'kewi', 'body' => $user->UserName . ' ' . 'Liked  Your Question', 'Key' => 'Notify');
        $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
        //---------------------------------------------------------------------
                  }
        return $this->apiResponse->setSuccess("  you Liked  Post successfully")->setData($post);

    }
    public function UnLikeAPost($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $postlike = Post_like::where('User_id', $user->id)->where('Post_id', $data['id'])->first();
        $post = Post::where('id', $data['id'])->first();
        if ($postlike) {
            $post->Likes = (int) $post->Likes - 1;
            $post->save();
            $postlike->delete();
            $post->forcefill(['ILiked' => false]);
        }else{
            return $this->apiResponse->setSuccess(" you already unliked this post")->setData($post);
        }


        return $this->apiResponse->setSuccess("  you UnLiked  Post successfully")->setData($post);

    }

    public function SavePost($data)
    {
        $user = GeneralHelper::getcurrentUser();

        $save = new Save;
        $save->Post_id = $data['id'];
        $save->User_id = $user->id;
        $save->save();
        return $this->apiResponse->setSuccess("  Post Saved  successfully")->setData($save);

    }
    public function DeletComment($data) //57@@

    {
        $user = GeneralHelper::getcurrentUser();
        try {
            $comment = Comment::where('id', $data['Comment_id'])->first();
            $Post = Post::where('User_id', $user->id)->where('id', $comment->Post_id)->first();
            if ($comment->User_id == $user->id) {
                $comment_delete = $comment->delete();
            } elseif ($Post != null) {
                $comment_delete = $comment->delete();} else {
                $comment_delete = false;

            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Comment Delete Successfuly")->setData($comment_delete);

    }
    public function DeletReplay($data) //58@@

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            $reply = Replay::where('id', $data['Replay_id'])->first();
            $Post = Post::where('User_id', $user->id)->where('id', $reply->Comment->Post_id)->first();
            if ($reply->User_id == $user->id) {
                $reply_delete = $reply->delete();
            } elseif ($Post != null) {
                $reply_delete = $reply->delete();} else {
                $reply_delete = false;

            }
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Comment Delete Successfuly")->setData($reply_delete);

    }
    public function DeletePost($data) //56@@

    {
        $user = GeneralHelper::getcurrentUser();
        try {

            $Post = Post::where('id', $data['Post_id'])->where('User_id', $user->id)->with('Comments')->delete();

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Post Delete Successfuly")->setData($Post);
    }

    /**
     * display community
     */
    public function FollowingPosts($data) //14

    { $user = GeneralHelper::getcurrentUser();
        $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
         $mysaved = Save::where('User_id', $user->id)->pluck('Post_id')->toarray();

        $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
        $posts = Post::wherein('User_id', $myfollowing_id)->with('Owner')->with(['Comments' => function ($query) {
            $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
        }])->withCount('Comments')->orderBy('id', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved) {
            $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
            if (in_array($item->id, $mysaved)) {
                $item->forcefill(['Saved' => true]);
            } else {
                $item->forcefill(['Saved' => false]);
            }
            if (in_array($item->id, $mylikes)) {
                $item->forcefill(['ILiked' => true]);
             } else {
                $item->forcefill(['ILiked' => false]);
             }

        });

        return $this->apiResponse->setSuccess(" my following fetched successfully")->setData($posts);

    }
    public function GetPosts($data,$request)

    {

        $user = GeneralHelper::getcurrentUser();
        $data['User_id'] = $user->id;
        try {
            $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
            $mysaved = Save::where('User_id', $user->id)->get()->pluck('Post_id')->toarray();
            $myfollowers = Follow::where('following', $user->id)->with('followers')->get()->pluck('id')->toarray();
            $myfollowing = Follow::where('follower', $user->id)->with('followings')->get()->pluck('id')->toarray();
            $Posts = Post::with('Owner')->with(['Comments' => function ($query) {
                $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
            }])->withCount('Comments')->orderBy('id', 'Desc')->paginate(5)->each(function ($item,$key) use ($mylikes, $mysaved, $myfollowers, $myfollowing) {

                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->forcefill(['IUnLiked' => false]);

                } else {
                    $item->forcefill(['ILiked' => false]);
                    $item->forcefill(['IUnLiked' => true]);
                }

                if (in_array($item->owner->id, $myfollowers)) {
                    $item->forcefill(['User_status' => 'follower']);

                }if (in_array($item->owner->id, $myfollowing)) {
                    $item->owner->forcefill(['User_status' => 'following']);

                }
                if (!in_array($item->owner->id, $myfollowing) and !in_array($item->owner->id, $myfollowers)) {
                    $item->owner->forcefill(['User_status' => 'anynoumes']);

                }

            });
        // $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($Posts);
        // $perPage = 5;
        // $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        // $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        // $paginatedItems->setPath($request->url());
    //     $perPage = 5;   
    // $page = Input::get('page', 1);
    // if ($page > count($Posts) or $page < 1) { $page = 1; }
    // $offset = ($page * $perPage) - $perPage;
    // $perPageUnits = array_slice($Posts,$offset,$perPage);
    // $pagination = Paginator::make($perPageUnits, count($Posts), $perPage);

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("User Posts Fetched Successfuly")->setData();

    }
    public function GetPostById($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            $likePost= Post_like::where('User_id',$user->id)->where('Post_id',$data['id'])->first();
            // dd($likePost);
            $mylikesComments=Comment_like::where('User_id',$user->id)->pluck('Comment_id')->toarray();

            $Post = Post::where('id', $data['id'])->with('owner')->with(['Comments' => function ($query) use($mylikesComments){
                $query->with('User')->with('Replaies')->with('Replaies.user');
            }])->withCount('Comments')->first();
            
            if($likePost){
                $Post->forcefill(['ILiked' => true]);
            }else{
                 $Post->forcefill(['ILiked' => false]);
            }
                  //check Anonymous
                 if($Post->owner->IsAnonymous==1){
                      $Post->owner->UserName="Anonymous";
                       $Post->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                 }
                 if($Post->IsAnonymous==1){
                        //check Anonymous
                      $Post->owner->UserName="Anonymous";
                       $Post->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                       
                 
                 }
            
            foreach($Post->comments as $comment){
                  
                     
                       if (in_array($comment->id, $mylikesComments)) {
                $comment->forcefill(['ILiked' => true]);
                        $comment->load('User');
                        

             } else {
                $comment->forcefill(['ILiked' => false]);
                    $comment->load('User');

             }
                     

                $comment->forcefill(['Created_at' => Carbon::parse($Post->Created_at)->diffForHumans()]);
             

            }
 
           

        } catch (\Exception $ex) {
            //return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Post Fetched Successfuly")->setData($Post);
    }
    public function GetMyPosts($data,$request)

    {
       
        try {
            $user = GeneralHelper::getcurrentUser();
            // if(!empty($data['id'])){
            //     $sharedPost = Shared_Post::where('User_id',$user->id)->pluck('Post_id')->toarray();

            //   $Posts = Post::where('IsAnonymous',0)->orwhere('User_id', $data['id'])->orwherein('id',$sharedPost)->orwhere('Asked_id',$data['id'])->with('Owner')->with('Comments')->withCount('Comments')->with('Comments.Replaies')->get()->each(function ($item, $key) {
            //     $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);

            // });
            // }else{
                 $sharedPost = Shared_Post::where('User_id',$user->id)->pluck('Post_id')->toarray();
                   $Posts = Post::where('Asked_id',"0")->where('IsAnonymous',"0")->where('User_id', $user->id)->orwherein('id',$sharedPost)->orderby('id','desc')->with('Owner')->with('Comments')->withCount('Comments')->with('Comments.Replaies')->get();
            
            
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Post Fetched Successfuly")->setData($Posts->paginate(8) );
    }
    public function GetMyAnswers($data,$request){
        try {
            // $user = GeneralHelper::getcurrentUser();

            // $Comments = Comment::where('User_id', $user->id)->orderBy('id', 'desc')->with(['user','post'=>function($query){
            // $query->withCount('Comments');
            // },'post.Owner','post.Comments','post.Comments.Owner','post.Comments.Replaies.Owner'])->paginate(10)->each(function ($item, $key)  {
            //     $item->post->forcefill(['Created_at' => Carbon::parse($item->post->Created_at)->diffForHumans()]);
            // });
            
            
            
            //******************
            $user = GeneralHelper::getcurrentUser();
                       $mylikesComment=Comment_like::where('User_id' , $user->id)->pluck('Comment_id')->toarray();
        $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $mysaved = Save::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $myfollowers_id = Follow::where('following', $user->id)->pluck('follower')->toarray();
        $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
         
    $Posts = Post::where('privacy','!=',1)->
                 with('Owner')->whereHas('Comments', function ( $query)use ($user) {
                $query->where('User_id',$user->id );
            })
            ->with(['Comments' => function ($q)  {
                $q->with('Replaies')->with('Replaies.Owner')->withCount('Replaies')->with('User')->orderBy('id','desc');
            }])->withCount('Comments')->with('Comments.User')->orderBy('id', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved,$user) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                   if($item->Owner )
             {
                
            $item->Owner->forcefill(['Status'=>"unfollowed"]);

                 
                
                  
             }
 if(!empty($item->comments)){
                    $mylikesComment=Comment_like::where('User_id' , $user->id)->pluck('Comment_id')->toarray();

                      $item->comments->each(function ($item,$value)use($user,$mylikesComment){
                          if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                         $item->Replaies->each(function($item,$value) {
                             
                            
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                  
                     });
                
                         });
                    
                 };;
            });
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($Posts);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());
        
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Post Fetched Successfuly")->setData(  $paginatedItems);

    }
      public function ShowUserAnswers($data,$request){
        try {
            $user = GeneralHelper::getcurrentUser();

        $mylikesComment=Comment_like::where('User_id' , $user['id'])->pluck('Comment_id')->toarray();
        $mylikes = Post_like::where('User_id', $data['id'])->pluck('Post_id')->toarray();
        $mysaved = Save::where('User_id', $data['id'])->pluck('Post_id')->toarray();
        $myfollowers_id = Follow::where('following', $data['id'])->pluck('follower')->toarray();
        $myfollowing_id = Follow::where('follower', $data['id'])->pluck('following')->toarray();
         $userId=$data['id'];
    $Posts = Post::where('privacy','!=',1)->
                 with('Owner')->whereHas('Comments', function ( $query)use ($userId) {
                $query->where('User_id',$userId );
            })
            ->with(['Comments' => function ($q)  {
                $q->with('Replaies')->with('Replaies.Owner')->withCount('Replaies')->with('User')->orderBy('id','desc');
            }])->withCount('Comments')->with('Comments.User')->orderBy('id', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved,$userId) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                   if($item->Owner )
             {
                
            $item->Owner->forcefill(['Status'=>"unfollowed"]);

                 
                
                  
             }
 if(!empty($item->comments)){
                    $mylikesComment=Comment_like::where('User_id' , $userId)->pluck('Comment_id')->toarray();

                      $item->comments->each(function ($item,$value)use($userId,$mylikesComment){
                          if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                         $item->Replaies->each(function($item,$value) {
                             
                            
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                  
                     });
                
                         });
                    
                 };;
            });
         $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($Posts);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Post Fetched Successfuly")->setData(  $paginatedItems);

    }
    public function ShowUserPosts($data,$request)

    {
       
        try {
                     $userId=$data['User_id'];

            $currentUser=GeneralHelper::getcurrentUser();
                      $mylikesComment=Comment_like::where('User_id' , $userId)->pluck('Comment_id')->toarray();
        $mylikes = Post_like::where('User_id', $userId)->pluck('Post_id')->toarray();
        $mysaved = Save::where('User_id', $userId)->pluck('Post_id')->toarray();
        $myfollowers_id = Follow::where('following', $userId)->pluck('follower')->toarray();
        $myfollowing_id = Follow::where('follower', $userId)->pluck('following')->toarray();
         $sharedPost = Shared_Post::where('User_id',$currentUser->id)->pluck('Post_id')->toarray();

    $posts = Post::where('Asked_id',"0")->where('IsAnonymous',"0")->where('User_id', $data['User_id'])->orwherein('id',$sharedPost)->where('Asked_id',"0")->where('IsAnonymous',"0")->orderby('id','desc')->
                 with('Owner')
            ->with(['Comments' => function ($q) use($myfollowing_id) {
                $q->with('Replaies')->with('Replaies.Owner')->withCount('Replaies')->with('User')->orderBy('id','desc');
            }])->withCount('Comments')->with('Comments.User')->orderBy('id', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved,$myfollowing_id) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                   if($item->owner )
             {
                     //check Anonymous
                //  if($item->IsAnonymous==1){
                //       $item->owner->UserName="Anonymous";
                //       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                //       if($item->comments){
                //              $item->owner->UserName="Anonymous";
                //       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                //       }
                //  }
                 $currentUser=GeneralHelper::getcurrentUser();
                 if(in_array($item->owner->id,$myfollowing_id) ){
                    $item->owner->forcefill(['Status'=>"followed"]);
                 } else if ($item->owner->id==$currentUser->id){
                    $item->owner->forcefill(['Status'=>"no"]);
                 }else{
                  $item->owner->forcefill(['Status'=>"unfollowed"]);

                 }
                 
          
                  
             }
 if(!empty($item->comments)){
                                           $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();

                      $item->comments->each(function ($item,$value)use($myfollowing_id,$currentUser,$mylikesComment){
                        $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                          if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                         $item->Replaies->each(function($item,$value)use($myfollowing_id,$currentUser){
                        $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);

                             if(in_array($item->user->id,$myfollowing_id) ){
                         
                    $item->user->forcefill(['Status'=>"followed"]);
                 } else if ($item->user->id==$currentUser->id){
                   $item->user->forcefill(['Status'=>"no"]);
                 }else{
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                 }
                     });
                
                         });
                    
                 };;
            });
            
                 $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($posts);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("User Posts Fetched Successfuly")->setData($paginatedItems);
    }
    public function MySavedPost($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $mysaved = Save::where('User_id', $user->id)->with(['post' => function ($query) {
            $query->with('Owner')->with(['Comments' => function ($query) {
                $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
            }])->withCount('Comments');
        }])->get();
        return $this->apiResponse->setSuccess("  your saved fetched   successfully")->setData($mysaved);

    }

    /**
     * reports community
     */

    public function MakePostReport($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $Post = Post::find($data['Post_id']);
            //  $type = "Post with id ".((string)$Post->id);
            $Report = Report::create(['Reporter_id' => $user->id, 'Reported_id' => $Post->User_id, 'Type' =>$data['type'],'Model'=>'Post','TargetId'=> $Post->id]);

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }

        return $this->apiResponse->setSuccess("Reported Post Done")->setData($Report);

    }

    public function MakeCommentReport($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $Comment = Comment::find($data['Comment_id']);
            //   $type = "Comment with id ".((string)$Comment->id);
            $Report = Report::create(['Reporter_id' => $user->id, 'Reported_id' => $Comment->User_id, 'Type' =>$data['type'],'Model'=>'Comment','TargetId'=>$Comment->id]);

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }

        return $this->apiResponse->setSuccess("Reported Comment Done")->setData($Report);

    }
    public function MakeReplyReport($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $Reply = Replay::find($data['Reply_id']);
            //   $type = "Reply with id ".((string)$Reply->id);
            $Report = Report::create(['Reporter_id' => $user->id, 'Reported_id' => $Reply->User_id, 'Type' =>$data['type'],'Model'=>'Reply','TargetId'=>$Reply->id]);

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }

        return $this->apiResponse->setSuccess("Reported Reply Done")->setData($Report);

    }

/**
 * profile (user)
 *
 *
 */
    public function ShowUserProfileById($data) //11

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            $User = User::where('id', $data['User_id'])->first();

            $myfollowers_count = Follow::where('following', $User->id)->with('followers')->get()->count();
            $myfollowing_count = Follow::where('follower', $User->id)->with('followings')->get()->count();
            $Posts_count = Post::where('User_id', $User->id)->withCount('Comments')->get()->count();
            $likes_Post =Post_like::where('User_id',$data['User_id'])->count();
            $likes_comment =Comment_like::where('User_id',$data['User_id'])->count();
            $likes_Replaies = Replay_like::where('User_id',$data['User_id'])->count();
          //  $x =  $likes_Post +$likes_comment+$likes_Replaies ;
           $x =  $likes_comment;

            $User->forcefill(['Likes_Count'=>$x]);

            $User->forcefill(['follwersCount' => $myfollowers_count, 'follwingCount' => $myfollowing_count, 'PostsCount' => $Posts_count]);
            

        

            $follow1 = Follow::where('follower', $data['User_id'])->where('following', $user->id)->first();
            $follow2 = Follow::where('follower', $user->id)->where('following', $data['User_id'])->first();
            if ($follow2) {
                $User['Status'] = 'followed';
            } 
            // elseif ($follow2) {
            //     $User['Status'] = "false";
            // }
            else {
                $User['Status'] = 'unfollowed';
            }

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("User Profile Fetched Successfuly")->setData($User);

    }

    public function GetAllUsers($data)

    {
        try {
            $users = User::all();
            $user = GeneralHelper::getcurrentUser();
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("users Fetched Successfuly")->setData($users);

    }

    public function LikeUserProfile($data) //14 *#

    {
        try {
            $user = GeneralHelper::getcurrentUser();

            $User = User::where('id', $data['User_id'])->first();
            if($User){
                    if ($User->id == $user->id) {
                    return $this->apiResponse->setSuccess("you can\'t Like your profile")->setData();
                    }
                    $User->Likes = (int) $User->Likes + 1;
                    $User->save();
                    LikeUserProfile::create(['Liker_id' => $user->id, 'Liked_id' => $User->id]);
        
                    $newUser = User::where('id', $data['User_id'])->first();
                    $likedUser = LikeUserProfile::where('Liker_id', $user->id)
                        ->where('Liked_id', $data['User_id'])->first();
        
                    if ($likedUser) {
        
                        $newUser['Liked'] = 1;
                    } else {
                        $newUser['Liked'] = 0;
                    }
        
                    GeneralHelper::SetNotfication($user->UserName . ' ' . 'Liked Your Profile', 'Like', 'UserProfile', $user->id, $User->id, $User->id, "Like");
        
                    //--------------------------FireBaseNotfication------------------------------------------
                    $TargetUser = User::where('id', $User->id)->first();
                    $data1 = array('title' => 'kewi', 'body' => $user->UserName . ' ' . 'Liked Your Profile', 'Key' => 'Notify');
                    $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
                    //---------------------------------------------------------------------
                
            }else{
                return $this->apiResponse->setSuccess("User Not Found")->setData();
            }
           
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Profile Liked Successfuly")->setData($newUser);

    }
    public function UpdateProfileDescription($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $User = User::find($user->id)->update($data);
            $user = User::find($user->id);

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError("Missing data ")->setData();
        }
        return $this->apiResponse->setSuccess("User Updated succesfully")->setData($user);

    }

    public function UploadProfileImage($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $User = User::find($user->id)->update($data);
            $user = User::find($user->id);

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError("Missing data ")->setData();
        }
        return $this->apiResponse->setSuccess("User Updated succesfully")->setData($user);

    }

    public function MakeUserReport($data) //40@@

    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $data['Reporter_id'] = $user->id;
            //    $r = Report::create($data);
            $Report = Report::create(['Reporter_id' => $user->id, 'Reported_id' => $data['Reported_id'], 'Type' =>$data['type'],'Model'=>'User','TargetId'=>$data['Reported_id']]);

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError( $ex)->setData();
        }
        return $this->apiResponse->setSuccess("Reporting Done")->setData($Report);

    }
    public function UpdateUserInformation($data)
    {

        try {
            $user = GeneralHelper::getcurrentUser();
            $User = User::find($user->id)->update($data);
            $user = User::find($user->id);
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError("Missing data ")->setData();
        }
        return $this->apiResponse->setSuccess("User Updated succesfully")->setData($user);

    }

    public function ShowMyInformation($data)

    {

        try {
            $user = GeneralHelper::getcurrentUser();

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("User Profile Fetched Successfuly")->setData($user);

    }

    public function UnLikeUserProfile($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();

            $User = User::where('id', $data['User_id'])->first();
            $User->Likes = (int) $User->Likes - 1;
            LikeUserProfile::where(['Liker_id' => $user->id, 'Liked_id' => $data['User_id']])->delete();
            $User->save();
            $newUser = User::where('id', $data['User_id'])->first();
            $likedUser = LikeUserProfile::where('Liker_id', $user->id)
                ->where('Liked_id', $data['User_id'])->first();

            if ($likedUser) {

                $newUser['Liked'] = 1;
            } else {
                $newUser['Liked'] = 0;
            }

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Profile unLiked Successfuly")->setData($newUser);

    }
    public function DeleteMyAccount($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            $User = User::where('id', $user->id)->delete();
        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("User Deleted Successfuly")->setData($User);
    }

    /**
     * message
     */

    public function SendMessage($data)

    {
        try {
            $user=GeneralHelper::getcurrentUser();
            //my blocked users 
            $blockedUserBySent = block::where('Blocker_id',$user->id)->where('Blocked_id',$data['Message_to'])->first();
            $blockedUserByRec= block::where('Blocker_id',$data['Message_to'])->where('Blocked_id',$user->id)->first();

             
          if($blockedUserBySent or $blockedUserByRec){
             return $this->apiResponse->setSuccess("You Can\'t send message to blocked User")->setData();

            }
            $user = GeneralHelper::getcurrentUser();
            $message = new Message();
            $message->Message = $data['Message'];
            $message->Message_From = $user->id;
            $message->Message_To = $data['Message_to'];
            $message->type=$data['type'];
            $message->save();
            GeneralHelper::SetNotfication($user->UserName . ' ' . 'Sent You New Message ', $message->Message, 'Message', $user->id, $message->Message_To, $message->id, 'NewMessage');
            //--------------------------FireBaseNotfication------------------------------------------
            $TargetUser = User::where('id', $message->Message_To)->first();
            $data1 = array('title' => 'kwei', 'body' => $user->UserName . ' ' . 'sent:'. $data['Message'], 'Key' => 'Message');
            $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
            //---------------------------------------------------------------------
        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Message sent Successfuly")->setData($message);

    }

    public function GetAllMessagesByUserId($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();
            $targetUser = User::where('id', $data['To'])->first();
            $TUser = array();
            $TUser['User'] = $targetUser;
            $messagesSource = Message::where('Message_From', $user->id)->where('Message_To', $data['To'])->orderBy('id', 'ASC')->get()->toArray();

            $messagesDestination = Message::where('Message_From', $data['To'])->where('Message_To', $user->id)->with('UserSent')->orderBy('id', 'ASC')->get()->toArray();
            $Conversion = array();
            // start  check sent or recive
            $CheckedMessageSource = array();
            $CheckedMessageDest = array();

            // end check sent or recive
            $Conversion = array_merge($messagesSource, $messagesDestination);
            usort($Conversion, function ($item1, $item2) {
                return $item1['id'] <=> $item2['id'];
            });
            $newConv = array();
            foreach ($Conversion as $conv) {
                if ($conv['Message_From'] == $user->id) {

                    $conv['MessageDelivery4'] = "ISent";
                    array_push($newConv, $conv);
                } else {

                    $conv['MessageDelivery4'] = "IRecived";
                    array_push($newConv, $conv);
                }

            }

            $newModArray = array();
            foreach ($newConv as $conv) {
                $date = Carbon::parse($conv['Created_at'])->diffForHumans();
                $conv['date'] = $date;
                array_push($newModArray, $conv);
            }

        } catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Messages Fetched Successfuly")->setData($newModArray);

    }

    public function ShowMessageById($data)

    {
        try {
            $user = GeneralHelper::getcurrentUser();

             Message::where('id', $data['id'])->update(['Seen' => 1, 'Seen_at' => Carbon::now()]);
             $m= Message::where('id', $data['id'])->first();
             $m->Seen=1;
             $m->Seen_at=Carbon::now();
             $m->save();
            $Message = Message::where('id', $data['id'])->with('UserSent')->with('UserRecived')->first();

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" Message Viewed And Marked As Read")->setData($Message);
    }
    public function GetFullConversions($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $allusers = User::where('id', '!=', $user->id)->get()->pluck('id')->toarray();
        $Messages=Message::where('Message_From',$user->id)->orwhere('Message_To',$user->id)->with('UserSent')->with('UserRecived')->orderBy('id', 'Desc')->distinct()->latest()->get();
        $Conversion=array();
           $keys=array();
        foreach($Messages as $message ){
               
           if(in_array($message->Message_From+$message->Message_To,$keys)){
           continue;
                
           }else{
                array_push($keys,$message->Message_From+$message->Message_To);
                array_push($Conversion,$message);
           }
        }
        $newConv = array();
        foreach ($Conversion as $conv) {
            if ($conv['Message_From'] == $user->id) {

                $conv['MessageDelivery4'] = "ISent";
                array_push($newConv, $conv);
            } else {

                $conv['MessageDelivery4'] = "IRecived";
                array_push($newConv, $conv);
            }

        }

        $newModArray = array();
        foreach ($newConv as $conv) {
            $date = Carbon::parse($conv['Created_at'])->diffForHumans();
            $conv['date'] = $date;
            array_push($newModArray, $conv);
        }
        $checked_To = array();
        $checked_From = array();
        $stack = array();
        $newp = array();
        foreach ($newModArray as $key => $arr) {

            if (!in_array($arr['Message_To'], $checked_To) and $arr['Message_From'] == $user->id) {
                array_push($checked_To, $arr['Message_To']);
            } else {
                if ($arr['Message_From'] == $user->id and in_array($arr['Message_To'], $checked_To)) {
                    unset($newModArray[$key]);
                    continue;
                }
            }
            if (!in_array($arr['Message_From'], $checked_From) and $arr['Message_To'] == $user->id) {
                array_push($checked_From, $arr['Message_From']);
            } else {
                if ($arr['Message_To'] == $user->id and in_array($arr['Message_From'], $checked_From)) {
                    unset($newModArray[$key]);
                    continue;
                }

            }
            array_push($newp, $newModArray[$key]);

        }
        

        return $this->apiResponse->setSuccess("   your full conversion fetched successfully ")->setData($newp);

    }

/**
 *
 * follow
 *  */
    public function Follow($data)

    {

        $user = GeneralHelper::getcurrentUser();
        $check = Follow::where('following', $data['user_id'])->where('follower',$user->id)->first();
        if ($check) {

            return $this->apiResponse->setSuccess(" you already a follower");

        }
        $follow = Follow::create(['follower' => $user->id, 'following' => $data['user_id']]);
        return $this->apiResponse->setSuccess(" Followed successfully")->setData($follow);

    }
    public function UnFollow($data)

    {
        $user = GeneralHelper::getcurrentUser();

        $follow = Follow::where('follower', $user->id)->where('following', $data['user_id'])->delete();
        return $this->apiResponse->setSuccess(" UnFollowed successfully");

    }
    public function MyFollowers($data,$request)
    {
        //my blocked account 
        
        $user = GeneralHelper::getcurrentUser();
        $myfollowing = Follow::where('follower', $user->id)->pluck('following')->toarray();
        $myfollowers = Follow::where('following', $user->id)->with('followers')->paginate(12)->each(function($item,$key )use ($myfollowing){
            if($item->followers){
        $myfollowing_count = Follow::where('follower', $item->followers->id)->get()->count();
        $myfollowers_count = Follow::where('following', $item->followers->id)->get()->count();
        $Posts_count = Post::where('User_id', $item->followers->id)->withCount('Comments')->get()->count();
        $likes_Post =Post_like::where('User_id',$item->followers->id)->count();
        $likes_comment =Comment_like::where('User_id',$item->followers->id)->count();
        $likes_Replaies = Replay_like::where('User_id',$item->followers->id)->count();
     //   $x =  $likes_Post +$likes_comment+$likes_Replaies ;
     $x =  $likes_comment;
        $item->followers->forcefill(['Likes_Count'=>$x]);
                     $currentUser = GeneralHelper::getcurrentUser();
         if(in_array($item->followers->id,$myfollowing) ){
                    $item->followers->forcefill(['Status'=>"followed"]);
                 } else if ($item->followers->id==$currentUser->id){
                    $item->followers->forcefill(['Status'=>"no"]);
                 }else{
                  $item->followers->forcefill(['Status'=>"unfollowed"]);

                 }

        $item->followers->forcefill(['follwersCount' => $myfollowers_count, 'follwingCount' => $myfollowing_count, 'PostsCount' => $Posts_count]);

            }
         });
               $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($myfollowers);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());
        return $this->apiResponse->setSuccess(" myfollowers fetched successfully")->setData($paginatedItems);

    }
    public function UserFollowers($data,$request)
    {
    $Userfollowing = Follow::where('follower', $data['id'])->pluck('following')->toarray();

        $myfollowers = Follow::where('following', $data['id'])->with('followers')->get()->each(function($item,$key )use($Userfollowing){
            if($item->followers){
        $myfollowing_count = Follow::where('follower', $item->followers->id)->get()->count();
        $myfollowers_count = Follow::where('following', $item->followers->id)->get()->count();
        $Posts_count = Post::where('User_id', $item->followers->id)->withCount('Comments')->get()->count();
        $likes_Post =Post_like::where('User_id',$item->followers->id)->count();
        $likes_comment =Comment_like::where('User_id',$item->followers->id)->count();
        $likes_Replaies = Replay_like::where('User_id',$item->followers->id)->count();
       // $x =  $likes_Post +$likes_comment+$likes_Replaies ;
         $x =  $likes_comment;
        $item->followers->forcefill(['Likes_Count'=>$x]);
                $currentUser = GeneralHelper::getcurrentUser();

         if(in_array($item->followers->id,$Userfollowing) ){
                    $item->followers->forcefill(['Status'=>"followed"]);
                 } else if ($item->followers->id==$currentUser->id){
                    $item->followers->forcefill(['Status'=>"no"]);
                 }else{
                  $item->followers->forcefill(['Status'=>"unfollowed"]);

                 }

        $item->followers->forcefill(['follwersCount' => $myfollowers_count, 'follwingCount' => $myfollowing_count, 'PostsCount' => $Posts_count]);

            }
         });
        //      $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($myfollowers);
        // $perPage = 8;
        // $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        // $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        // $paginatedItems->setPath($request->url());
        return $this->apiResponse->setSuccess(" myfollowers fetched successfully")->setData($myfollowers->paginate(8));

    }
    public function Following($data,$request)

    {
        
        $user = GeneralHelper::getcurrentUser();
        $myfollowing1 = Follow::where('follower', $user->id)->pluck('following')->toarray();

        $myfollowing = Follow::where('follower', $user->id)->with('followings')->paginate(12)->each(function($item,$key)use($myfollowing1){
            if($item->followings){
            $myfollowing_count = Follow::where('follower', $item->followings->id)->get()->count();
            $myfollowers_count = Follow::where('following', $item->followings->id)->get()->count();
            $Posts_count = Post::where('User_id', $item->followings->id)->withCount('Comments')->get()->count();
            $likes_Post =Post_like::where('User_id',$item->followings->id)->count();
            $likes_comment =Comment_like::where('User_id',$item->followings->id)->count();
            $likes_Replaies = Replay_like::where('User_id',$item->followings->id)->count();
           // $x =  $likes_Post +$likes_comment+$likes_Replaies ;
             $x =  $likes_comment;
            $item->followings->forcefill(['Likes_Count'=>$x]);
                    $currentUser = GeneralHelper::getcurrentUser();

 if(in_array($item->followings->id,$myfollowing1) ){
                    $item->followings->forcefill(['Status'=>"followed"]);
                 } else if ($item->followers->id==$currentUser->id){
                    $item->followings->forcefill(['Status'=>"no"]);
                 }else{
                  $item->followings->forcefill(['Status'=>"unfollowed"]);

                 }
            $item->followings->forcefill(['follwersCount' => $myfollowers_count, 'follwingCount' => $myfollowing_count, 'PostsCount' => $Posts_count]);
  
            }
         });
                      $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($myfollowing);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());
        return $this->apiResponse->setSuccess(" my following fetched successfully")->setData($paginatedItems);

    }
      public function UserFollowing($data,$request)

    {
        $user = GeneralHelper::getcurrentUser();
    $Userfollowing =Follow::where('follower', $data['id'])->pluck('following')->toarray();
 
        $myfollowing = Follow::where('follower', $data['id'])->with('followings')->get()->each(function($item,$key)use($Userfollowing){
            if($item->followings){
            $myfollowing_count = Follow::where('follower', $item->followings->id)->get()->count();
            $myfollowers_count = Follow::where('following', $item->followings->id)->get()->count();
            $Posts_count = Post::where('User_id', $item->followings->id)->withCount('Comments')->get()->count();
            $likes_Post =Post_like::where('User_id',$item->followings->id)->count();
            $likes_comment =Comment_like::where('User_id',$item->followings->id)->count();
            $likes_Replaies = Replay_like::where('User_id',$item->followings->id)->count();
           // $x =  $likes_Post +$likes_comment+$likes_Replaies ;
           $x =  $likes_comment;
            $item->followings->forcefill(['Likes_Count'=>$x]);
                    $currentUser = GeneralHelper::getcurrentUser();
         if(in_array($item->followings->id,$Userfollowing) ){
                    $item->followings->forcefill(['Status'=>"followed"]);
                 } else if ($item->followings->id==$currentUser->id){
                    $item->followings->forcefill(['Status'=>"no"]);
                 }else{
                  $item->followings->forcefill(['Status'=>"unfollowed"]);

                 }

            $item->followings->forcefill(['follwersCount' => $myfollowers_count, 'follwingCount' => $myfollowing_count, 'PostsCount' => $Posts_count]);
  
            }
         });
        //                       $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($myfollowing);
        // $perPage = 8;
        // $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        // $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        // $paginatedItems->setPath($request->url());
        return $this->apiResponse->setSuccess(" my following fetched successfully")->setData($myfollowing->paginate(8));

    }
    /**
     * story
     *
     */

    public function AddStroy($data)
    {
        $user = GeneralHelper::getcurrentUser();

        $story = new Story;
        if (!empty($data['text'])) {
            $story->text = $data['text'];
            $story->User_id = $user->id;
        }
        if (!empty($data['photo'])) {
            $story->photo = $data['photo'];
            if(!empty($data['Photo']))
            studio::create(['User_id'=>$user->id,'Photo'=>$data['photo'],'Date'=>Carbon::now()->format('Y-m-d')]);
            $story->User_id = $user->id;
        }


        $story->save();
        //---------------------------------------------------- send push notfication to all following ---------------------------//
        $myfollowings = follow::where('following', $user->id)->get();

        foreach ($myfollowings as $following) {
            GeneralHelper::SetNotfication($user->UserName . ' ' . ' posted new  Story',' new story', 'story', $user->id, $following->follower, $following->id, "story");
            //--------------------------FireBaseNotfication------------------------------------------
            $targetUser = User::where('id', $following->follower)->first();
            $data1 = array('title' => 'Kewi App', 'body' => $user->UserName . ' ' . 'Posted a New  Story', 'Key' => 'Notify');
            $res = FCMHelper::sendFCMMessage($data1, $targetUser->Token);
            //---------------------------------------------------------------------
        }

        return $this->apiResponse->setSuccess("your Story  posted successfully")->setData($story);

    }

    public function showStoryByid($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $stories = Story::where('id', $data['id'])->with('owner')->get();
        return $this->apiResponse->setSuccess("  your story published successfully")->setData($stories);

    }
    public function UserStory($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $stories = Story::where('User_id', $data['User_id'])->with('owner')->get();
        return $this->apiResponse->setSuccess("  your story published successfully")->setData($stories);

    }

    /**
     * get posts orderby comment count
     */

    public function Trends($data,$request)
    {
                 $user=GeneralHelper::getcurrentUser();
                 //my blocked users 
                 $blockedUsers = block::where('Blocker_id',$user->id)->pluck('Blocked_id')->toarray();

         //order by comments_counts , order by comments Likes
        $data['User_id'] = $user->id;
        try {
            $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
             $mysaved = Save::where('User_id', $user->id)->get()->pluck('Post_id')->toarray();
            $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();

            $Posts = Post::wheredoesnthave('Owner',function($query) use($blockedUsers){
                $query->wherein('id',$blockedUsers);
            })->where('Asked_id',"0")->where('IsAnonymous',"0")->with('Owner')->with(['Comments' => function ($query) {
                $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
            }])->withCount('Comments')->orderBy('Comments_count', 'desc')->orderBy('Likes', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved,$myfollowing_id) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                } else {
                    $item->forcefill(['ILiked' => false]);
                 }
                 
                     if($item->Owner )
             {
                     //check Anonymous
                 if($item->IsAnonymous==1){
                      $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.Svg';
                       if($item->comments){
                             $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.Svg';
                       }
                 }
                 $currentUser=GeneralHelper::getcurrentUser();
                 if(in_array($item->Owner->id,$myfollowing_id) ){
                    $item->Owner->forcefill(['Status'=>"followed"]);
                 } else if ($item->Owner->id==$currentUser->id){
                    $item->Owner->forcefill(['Status'=>"no"]);
                 }else{
                  $item->Owner->forcefill(['Status'=>"unfollowed"]);

                 }
             }
            if(!empty($item->comments)){
                                           $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();

                      $item->comments->each(function ($item,$value)use($myfollowing_id,$currentUser,$mylikesComment){
                                                  $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);

                          if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                    $mylikesReplaies=Replay_like::where('User_id' , $currentUser->id)->pluck('Replay_id')->toarray();

                        $item->Replaies->each(function($item,$value)use($myfollowing_id,$currentUser,$mylikesReplaies){
                        $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                        if (in_array($item->id, $mylikesReplaies)) {
                            $item->forcefill(['ILiked' => true]);
                        }else{
                            $item->forcefill(['ILiked' => false]);
                        }
                             if(in_array($item->user->id,$myfollowing_id) ){
                         
                    $item->user->forcefill(['Status'=>"followed"]);
                 } else if ($item->user->id==$currentUser->id){
                   $item->user->forcefill(['Status'=>"no"]);
                 }else{
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                 }
                     });
                
                         });
                    
                 };;
            });
        //       $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($Posts);
        // $perPage = 8;
        // $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        // $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        // $paginatedItems->setPath($request->url());

            return $this->apiResponse->setSuccess("Trends")->setData($Posts->paginate(8));

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
    }
    /**
     * news display all  recently  posts  in app
     */

    public function News($data,$request)
    {
         $user = GeneralHelper::getcurrentUser();
        //my blocked users 
         $blockedUsers = block::where('Blocker_id',$user->id)->pluck('Blocked_id')->toarray();

            // privacy 0 (if question is public ) we will not appear wit privacy 2 ( just appear for followers and following )
       
        $data['User_id'] = $user->id;
        try {
            $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
            $mysaved = Save::where('User_id', $user->id)->get()->pluck('Post_id')->toarray();
          
            $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
            // privacy 0 (if question is public )
            $Posts = Post::wheredoesnthave('Owner',function($query) use($blockedUsers){
                $query->wherein('id',$blockedUsers);
            })->where('privacy','!=',1)->where('Asked_id',"0")->where('IsAnonymous',"0")->with('Owner')->with(['Comments' => function ($query) {
                $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
            }])->withCount('Comments')->orderBy('id', 'desc')->get()    ->each(function ($item, $key) use ($mylikes, $mysaved,$myfollowing_id) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                 //check Anonymous
                 if($item->IsAnonymous==1){
                      $item->owner->UserName="Anonymous";
                      $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                      if($item->comments){
                             $item->owner->UserName="Anonymous";
                      $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                      }
                 }
                  if($item->Owner )
             {
                 
                  $currentUser=GeneralHelper::getcurrentUser();
                 if(in_array($item->Owner->id,$myfollowing_id) ){
                  $item->Owner->forcefill(['Status'=>"followed"]);
                 } else if ($item->Owner->id==$currentUser->id){
                    $item->Owner->forcefill(['Status'=>"no"]);
                 }else{
                  $item->Owner->forcefill(['Status'=>"unfollowed"]);
                 }
             }
             
            if(!empty($item->comments)){
                $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();
                

                $item->comments->each(function ($item,$value)use($myfollowing_id,$currentUser,$mylikesComment){
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);

                if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                        $mylikesReplaies=Replay_like::where('User_id' , $currentUser->id)->pluck('Replay_id')->toarray();
                         $item->Replaies->each(function($item,$value)use($myfollowing_id,$currentUser,$mylikesReplaies){
                                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                            if(in_array($item->id ,$mylikesReplaies )){
                                 $item->forcefill(['ILiked' => true]);
                            }else{
                                 $item->forcefill(['ILiked' => false]);
                            }
                             if(in_array($item->user->id,$myfollowing_id) ){
                         
                    $item->user->forcefill(['Status'=>"followed"]);
                 } else if ($item->user->id==$currentUser->id){
                  $item->user->forcefill(['Status'=>"no"]);
                 }else{
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                 }
                     });
                
                         });
                    
                 };;
            });
            
            
            
        
    //   $currentPage = LengthAwarePaginator::resolveCurrentPage();
    //     $itemCollection = collect($Posts);
    //     $perPage = 4;
    //     $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
    //     $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
    //     $paginatedItems->setPath($request->url());

            return $this->apiResponse->setSuccess(" your news ")->setData($Posts->paginate(8));

        } catch (\Illuminate\Database\QueryException $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
    }
    public function HomePage($data,$request)
    {
        $user = GeneralHelper::getcurrentUser();
        $currentUser=GeneralHelper::getcurrentUser();
        $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();
        $mylikesReplaies=Replay_like::where('User_id' , $currentUser->id)->pluck('Replay_id')->toarray();
        $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $mysaved = Save::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $myfollowers_id = Follow::where('following', $user->id)->pluck('follower')->toarray();
        $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
        $blockedUsers = block::where('Blocker_id',$user->id)->pluck('Blocked_id')->toarray();

        $Posts = Post::where('privacy','!=',1)->where('Asked_id',"0")->where('IsAnonymous',"0")->
                 with('Owner')->whereHas('Comments', function ( $qu) use($myfollowing_id){
                $qu->wherein('User_id', $myfollowing_id);
                })->wheredoesnthave('Owner',function($query) use($blockedUsers){
                $query->wherein('id',$blockedUsers);
            })
            ->with(['Comments' => function ($q) use($myfollowing_id) {
                $q->with('Replaies')->with('Replaies.Owner')->withCount('Replaies')->with('Owner')->orderBy('id','desc');
            }])->withCount('Comments')->with('Comments.User')->orderBy('id', 'desc')->paginate(10)->each(function ($item, $key) use ($mylikes, $mysaved,$myfollowing_id,$mylikesReplaies) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                   if($item->owner )
             {
                     //check Anonymous
                 if($item->IsAnonymous==1){
                      $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                       if($item->comments){
                             $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.jpg';
                       }
                 }
                 $currentUser=GeneralHelper::getcurrentUser();
                 if(in_array($item->owner->id,$myfollowing_id) ){
                    $item->owner->forcefill(['Status'=>"followed"]);
                 } else if ($item->owner->id==$currentUser->id){
                    $item->owner->forcefill(['Status'=>"no"]);
                 }else{
                  $item->owner->forcefill(['Status'=>"unfollowed"]);

                 } }
                    if(!empty($item->comments)){
                    $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();
                    $item->comments->each(function ($item,$value)use($myfollowing_id,$currentUser,$mylikesComment,$mylikesReplaies){
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.Owner');
                    }else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.Owner');
                    }
                    $item->Replaies->each(function($item,$value)use($myfollowing_id,$currentUser,$mylikesReplaies){
                              if (in_array($item->id, $mylikesReplaies)) {
                    $item->forcefill(['ILiked' => true]);

                    }  else {
                        $item->forcefill(['ILiked' => false]);
                    }
                 
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if(in_array($item->user->id,$myfollowing_id) ){
                    $item->user->forcefill(['Status'=>"followed"]);
                    } else if ($item->user->id==$currentUser->id){
                        $item->user->forcefill(['Status'=>"no"]);
                    }else{
                     $item->user->forcefill(['Status'=>"unfollowed"]);
                    }
                    });
                
                         });
                    
                 };;
            });
           
           
        // $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($Posts);
        // $perPage = 8;
        // $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        // $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        // $paginatedItems->setPath($request->url());
            
            
    //  $newposts=$Posts;
    //         foreach($newposts as $post){
    //             foreach($post->comments as $comment){
                     
    //                 if($comment->id==164){
    //                     $comment->Iliked="true";
    //                     $comment->load('Replaies');
    //                 }
    //             }
    //         }
      

        return $this->apiResponse->setSuccess(" Homepage fetched successfully")->setData($Posts->paginate(8));
    }
    public function searchFriends($data)
    {
        $users = User::where('UserName', 'like', '%' . $data['UserName'] . '%')->get();
        return $this->apiResponse->setSuccess("  search friends success ")->setData($users);

    }
    public function LikeAcomment($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $found = Comment_like::where('User_id' , $user->id)->where('Comment_id' ,$data['Comment_id'])->first();
        if($found)
        {
            return $this->apiResponse->setError("You Liked Comment Before")->setData();
        }
        $user = GeneralHelper::getcurrentUser();
        $comment = Comment::where('id' , $data['Comment_id'])->first();
                $post=Post::where('id',$comment->Post_id)->first();

        $comment->Likes = (int) $comment->Likes +1;
        // dd($comment->Likes);
        $comment->save();
        Comment_like::create(['User_id'=>$user->id , 'Comment_id' =>$comment->id]);
        $comment->forcefill(['ILiked' => true]);
        //set in user activity
         $target_user=User::where('id',$comment->User_id)->first();
        LikeActivity::create(['User_id'=>$user->id,'Model'=>'Comment','Target_id'=>$comment->id,'Date'=>Carbon::now()->format('Y-m-d'),'Body'=>$user->UserName.' Liked  '.$target_user->UserName .'\'s'.'Replay']);
//--------fcm--------------------------//

          if($user->QuestionNotify==0){

        GeneralHelper::SetNotfication($user->UserName . ' ' . 'Liked  Your Answer', 'Like', 'Like', $user->id, $comment->User_id, $post->id, "Like");
        //--------------------------FireBaseNotfication------------------------------------------
        $TargetUser = User::where('id', $comment->User_id)->first();
        $data1 = array('title' => 'kewi', 'body' => $user->UserName . ' ' . 'Liked  Your Replay', 'Key' => 'Notify');
        $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
        //---------------------------------------------------------------------
                  }
        return $this->apiResponse->setSuccess("  you Liked  Post successfully")->setData($comment);


    }
    public function LikeAReplay($data)
    {

        $user = GeneralHelper::getcurrentUser();
        $found = Replay_like::where('User_id' , $user->id)->where('Replay_id' , $data['Replay_id'])->first();
        if($found){
            return $this->apiResponse->setError("You Liked Replay Before")->setData();
        }
        $user = GeneralHelper::getcurrentUser();
        $replay = Replay::where('id',$data['Replay_id'])->first();
        $comment=Comment::where('id',$replay->Comment_id)->first();

        $post=Post::where('id',$comment->Post_id)->first();
        $replay->Likes = (int) $replay->Likes +1;
        $replay->save();
        Replay_like::create(['User_id'=>$user->id , 'Replay_id'=>$replay->id]);
        $replay->forcefill(['ILiked' => true]);
        
        //--------fcm--------------------------//
          if($user->QuestionNotify==0){

        GeneralHelper::SetNotfication($user->UserName . ' ' . 'Liked  Your Comment', 'Like', 'Like', $user->id, $replay->User_id, $post->id, "Like");
        //--------------------------FireBaseNotfication------------------------------------------
        $TargetUser = User::where('id', $replay->User_id)->first();
        $data1 = array('title' => 'kewi', 'body' => $user->UserName . ' ' . 'Liked  Your Comment', 'Key' => 'Notify');
        $res = FCMHelper::sendFCMMessage($data1, $TargetUser->Token);
        //---------------------------------------------------------------------
                  }
        return $this->apiResponse->setSuccess("You Liked Replay Succeessfuly")->setData($replay);
    }
    public function unLikecomment($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $commentlike = Comment_like::where('User_id', $user->id)->where('Comment_id', $data['id'])->first();
        $comment = Comment::where('id', $data['id'])->first();
        if ($commentlike) {
            $comment->Likes = (int) $comment->Likes - 1;
            $comment->save();
            $commentlike->delete();
            $comment->forcefill(['ILiked' => false]);
        }else{
            return $this->apiResponse->setSuccess(" you already unliked this comment")->setData($comment);
        }


        return $this->apiResponse->setSuccess("  you UnLiked  comment successfully")->setData($comment);

    }
    public function unLikeReplay($data)
    {
        $user = GeneralHelper::getcurrentUser();
        $replaylike = Replay_like::where('User_id', $user->id)->where('Replay_id', $data['id'])->first();
        // dd($replaylike);
        $replay = Replay::where('id', $data['id'])->first();
        // dd($replay);
        if($replaylike) {
            // dd($replaylike);
            $replay->Likes = (int) $replay->Likes - 1;
            $replay->save();
            $replaylike->delete();
            $replay->forcefill(['ILiked' => false]);
        }else{
            return $this->apiResponse->setSuccess(" you already unliked this Replay")->setData($replay);
        }


        return $this->apiResponse->setSuccess("  you UnLiked  Replay successfully")->setData($replay);

    }
    public function BlockUser($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            $blockedUser = block::where('Blocker_id',$user->id)->where('Blocked_id',$data['Blocked_id'])->first();
            //remove if i follow him or following me 
            $followingme= Follow::where('follower', $user->id)->where('following',$data['Blocked_id'])->first();
            if($followingme){
                  Follow::find($followingme->id)->delete();
              }
              $followedbyme= Follow::where('follower', $data['Blocked_id'])->where('following',$user->id)->first();
              if($followedbyme){
                  Follow::find($followedbyme->id)->delete();
              }
            if($blockedUser){
                return $this->apiResponse->setError("You Blocked this User before")->setData();
            }else{
                block::create(['Blocker_id'=>$user->id , 'Blocked_id'=>$data['Blocked_id']]);
                return $this->apiResponse->setSuccess("You Blocked this user successfuly")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }


    }
    public function getMyBlocked($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            $blockedUsers = block::where('Blocker_id',$user->id)->with('BlockedUsers')->get();


        }catch (\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
            return $this->apiResponse->setSuccess("BlockedAccounts Fetched Successfuly")->setData($blockedUsers);

    }
    public function AddInterest($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                // $interestArr = array();
                // $interestArr = $data['Interest_id'];
                $InterestIds= $data['Interest_id'];
                $InterestIds = (explode(",",$InterestIds));
                // dd( $InterestIds);
                foreach($InterestIds as $arr){
                    // dd($arr);
                    $interest = Interst::where('id' , $arr)->first();
                    if($interest){
                        $found = UserInterst::where('User_id',$user->id)->where('Interst_id',$interest->id)->first();
                        // dd($found);
                        if($found){
                            return $this->apiResponse->setError("You Added this interest before");
                        }else{
                            UserInterst::create(['User_id'=>$user->id , 'Interst_id'=>$interest->id]);
                        }

                    }else{
                        return $this->apiResponse->setError("Not found this interet");
                    }



                }
            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }



        }catch(\Exception $ex){
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Added Interested  Successfuly")->setData();
    }
    public function DeleteANotification($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            $notification = Notfication::where('User_id',$user->id)->where('id',$data['Notification_id'])->first();
            if($notification){
                $notification->delete();
            }else{
                return $this->apiResponse->setError("This Notification not found");
            }

        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Deleted Notification Successfuly")->setData();
    }
    public function ChangePassword($data)
    {
        try{
            $data['NewPass'] = app('hash')->make($data['NewPass']);
            $user = GeneralHelper::getcurrentUser();
            if ($user) {
                $check = Hash::check($data['CurrentPass'], $user->Password);
                if ($check) {
                    $user->Password = $data['NewPass'];
                    $user->save();
                }else{
                    return $this->apiResponse->setError("Your Current Password Not Correct");
                }
            }


        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
            return $this->apiResponse->setSuccess("Your Password changed successfuly")->setData($user);
    }
    public function GetIntersts($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            $intersts = Interst::all();
            $myInterestes = UserInterst::where('User_id',$user->id)->get();
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
            return $this->apiResponse->setSuccess("Intersts fetched successfuly")->setData(['AllInterests' => $intersts, 'MyInterests' => $myInterestes]);


    }
    public function ChangeProfilePicture($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $User = User::where('id',$user->id)->first();
                $User->Photo = $data['Photo'];

                $User->save();
                if(!empty($data['Photo']))
                studio::create(['User_id'=>$user->id,'Photo'=>$data['Photo'],'Date'=>Carbon::now()->format('Y-m-d')]);

                $user = User::find($user->id);
                // dd($user);

            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Changed Picture successfuly")->setData($user);
    }
    public function ChangeBackgroundPic($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $User = User::where('id',$user->id)->first();
                $User->BackgroundPhoto = $data['BackgroundPhoto'];
                if(!empty($data['BackgroundPhoto']))
                studio::create(['User_id'=>$user->id,'Photo'=>$data['BackgroundPhoto'],'Date'=>Carbon::now()->format('Y-m-d')]);

                $User->save();
                $user = User::find($user->id);
                // dd($user);

            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("changed Background successfuly")->setData($user);

    }
    public function UpdateStatus($data)
    {

        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $User = User::find($user->id);
                $User->Status=$data['Status'];
                $User->save();
                // dd($User);

            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" successfuly")->setData($User);
    }
    public function EditProfile($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $User = User::find($user->id)->update($data);
                $user = User::find($user->id);
                // dd($user);

            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("EditProfile successfuly")->setData($user);
    }
    public function GetMyStudio($data)
    {

        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                 $photos=studio::where('User_id', $user->id)->select('Photo','Date')->paginate(20);
                 if(empty($photos)){
                     return $this->apiResponse->setError("Not Found Photos");
                }else{
                    return $this->apiResponse->setSuccess("Photos fetched successfuly")->setData($photos);
                }


            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" successfuly")->setData($photos);
    }
    public function GetUserStudio($data)
    {
        try{
            $user = User::where('id',$data['User_id'])->first();
            if($user){
                $photos=studio::where('User_id', $user->id)->select('Photo','Date')->paginate(20);
                 if(empty($photos)){
                     return $this->apiResponse->setError("Not Found Photos");
                }else{
                    return $this->apiResponse->setSuccess("Photos fetched successfuly")->setData($photos);
                }


            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
    }
    public function GetMyLikes($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $Likedposts = Post_like::where('User_id',$user->id)->orderBy('Created_at','DESC')->pluck('Post_id')->toArray();
                $posts = Post::wherein('id',$Likedposts)->with('Owner' )->with(['Comments' => function($query){
                    $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
                }])->withCount('Comments')->orderBy('Created_at','DESC')->paginate(8)->each(function ($item, $key) use ($Likedposts) {
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if (in_array($item->id, $Likedposts)) {
                        $item->forcefill(['I Liked' => true]);
                    } else {
                        $item->forcefill(['I Liked' => false]);
                    }
                });
                $LikedComments = Comment_like::where('User_id',$user->id)->orderBy('Created_at','DESC')->pluck('Comment_id')->toArray();
                // dd($LikedComments);
                $comments = Comment::wherein('id',$LikedComments)->with('User')->with(['post' => function($query){
                        $query->with('Owner');
                }])->with('Replaies.Owner')->withCount('Replaies')->orderBy('Created_at','DESC')->paginate(8)->each(function ($item, $key) use ($LikedComments) {
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if (in_array($item->id, $LikedComments)) {
                        $item->forcefill(['I Liked' => true]);
                    } else {
                        $item->forcefill(['I Liked' => false]);
                    }
                });
                $LikedReplaies = Replay_like::where('User_id' , $user->id)->orderBy('Created_at','DESC')->pluck('Replay_id');
                // dd($LikedReplaies);
                $Replaies = Replay::wherein('id',$LikedReplaies)->orderBy('Created_at','DESC')->with('Owner')->with(['Comment' => function($query){
                        $query->with('User')->with('post.Owner');
                    }])->paginate(8);

            }else{
                return $this->apiResponse->setError("Not Found User!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Likes fetched successfuly")->setData(['Posts' => $posts,
                                                                                    'Comments' => $comments,
                                                                                    'Replaies' =>$Replaies
        ]);
    }
     public function GetUserLikes($data)
    {

        try{
            
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $Likedposts = Post_like::where('User_id',$user->id)->orderBy('Created_at','DESC')->pluck('Post_id')->toArray();
                $posts = Post::wherein('id',$Likedposts)->with('Owner' )->with(['Comments' => function($query){
                    $query->with('User')->with('Replaies.Owner')->withCount('Replaies');
                }])->withCount('Comments')->orderBy('Created_at','DESC')->paginate(8)->each(function ($item, $key) use ($Likedposts) {
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if (in_array($item->id, $Likedposts)) {
                        $item->forcefill(['I Liked' => true]);
                    } else {
                        $item->forcefill(['I Liked' => false]);
                    }
                });
                $LikedComments = Comment_like::where('User_id',$user->id)->orderBy('Created_at','DESC')->pluck('Comment_id')->toArray();
                // dd($LikedComments);
                $comments = Comment::wherein('id',$LikedComments)->with('User')->with(['post' => function($query){
                        $query->with('Owner');
                }])->with('Replaies.Owner')->withCount('Replaies')->orderBy('Created_at','DESC')->paginate(8)->each(function ($item, $key) use ($LikedComments) {
                    $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                    if (in_array($item->id, $LikedComments)) {
                        $item->forcefill(['I Liked' => true]);
                    } else {
                        $item->forcefill(['I Liked' => false]);
                    }
                });
                $LikedReplaies = Replay_like::where('User_id' , $user->id)->orderBy('Created_at','DESC')->pluck('Replay_id');
                // dd($LikedReplaies);
                $Replaies = Replay::wherein('id',$LikedReplaies)->orderBy('Created_at','DESC')->with('Owner')->with(['Comment' => function($query){
                        $query->with('User')->with('post.Owner');
                    }])->paginate(8);

            }else{
                return $this->apiResponse->setError("Not Found User!");
            }




        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess("Likes fetched successfuly")->setData(['Posts' => $posts,
                                                                                    'Comments' => $comments,
                                                                                    'Replaies' =>$Replaies
        ]);
    }
    public function FindFriend($data){
          $user = GeneralHelper::getcurrentUser();

        $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();

        $users=User::wherenotin('id',$myfollowing_id)->where('id','!=',$user->id)->get();
        // dd($users);
        foreach($users as $us){
            $checkFollow = Follow::where('follower',$user->id)->where('following',$us->id)->first();
            // dd($checkFollow->Status);
            if($checkFollow){
                $us->Status = "followed";
            }else{
                $us->Status = "unfollowed";
            }
        }
        return $this->apiResponse->setSuccess("Friends fetched successfuly")->setData($users);
       

    }
    public function LikeActivity($data){
        $user = GeneralHelper::getcurrentUser();
        $activites= LikeActivity::where('User_id',$user->id)->select('*')->paginate(12);
        /*
        *->groupBy(function($d){
            return Carbon::parse($d->Date)->diffForHumans();
        })->each(function($items,$value){
            foreach($items as $item){
                $item->Date=Carbon::parse($item->Date)->diffForHumans();
            }
        });
        */
        return $this->apiResponse->setSuccess("Like Activites fetched successfuly")->setData($activites);
    }
    public function UserLikeActivity($data){
        $user = GeneralHelper::getcurrentUser();
        $activites= LikeActivity::where('User_id',$data['id'])->select('*')->paginate(12);
        return $this->apiResponse->setSuccess("Like Activites fetched successfuly")->setData($activites);
    }
    public function loginGoogle($data)
    {
      $googleId = User::where('GoogleId', $data['GoogleId'])->first();

      if ($googleId) {
          $googleId->Token=$data['Token'];
          $googleId->save();
          return $this->apiResponse->setSuccess("User Found ")->setData($googleId);
      } else {
          $data['ApiToken'] = base64_encode(str_random(40));
          try {
              $user = User::create($data);
              $user->GoogleId = $data['GoogleId'];

              if ($user->Photo == null) {
                  $user->Photo = "";
              }

              $user->save();
          } catch (Exception $ex) {
              return $this->apiResponse->setError("Missing data ", $ex)->setData();
          }

          return $this->apiResponse->setSuccess("User Created successfuly ")->setData($user);
      }
  }
    public function Search($data,$request)
    {
        $users = User::where('UserName', 'like', '%' . $data['key'] . '%')->get();
        
        
        
       // $posts = Post::where('Post', 'like', '%' . $data['key'] . '%')->get();
         $user = GeneralHelper::getcurrentUser();
           $currentUser=GeneralHelper::getcurrentUser();
                      $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();
        $mylikes = Post_like::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $mysaved = Save::where('User_id', $user->id)->pluck('Post_id')->toarray();
        $myfollowers_id = Follow::where('following', $user->id)->pluck('follower')->toarray();
        $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
         
    $posts = Post::where('privacy','!=',1)->where('Post', 'like', '%' . $data['key'] . '%')->
                 with('Owner')
            ->with(['Comments' => function ($q) use($myfollowing_id) {
                $q->with('Replaies')->with('Replaies.Owner')->withCount('Replaies')->with('User')->orderBy('id','desc');
            }])->withCount('Comments')->with('Comments.User')->orderBy('id', 'desc')->paginate(8)->each(function ($item, $key) use ($mylikes, $mysaved,$myfollowing_id) {
                $item->forcefill(['Created_at' => Carbon::parse($item->Created_at)->diffForHumans()]);
                if (in_array($item->id, $mysaved)) {
                    $item->forcefill(['Saved' => true]);
                } else {
                    $item->forcefill(['Saved' => false]);
                }
                if (in_array($item->id, $mylikes)) {
                    $item->forcefill(['ILiked' => true]);

                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                   if($item->Owner )
             {
                          //check Anonymous
                 if($item->IsAnonymous==1){
                      $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.Svg';
                       if($item->comments){
                             $item->owner->UserName="Anonymous";
                       $item->owner->Photo='ProjectFiles/UserPhotos/Anonymous.Svg';
                       }
                 }
                 $currentUser=GeneralHelper::getcurrentUser();
                 if(in_array($item->Owner->id,$myfollowing_id) ){
                    $item->Owner->forcefill(['Status'=>"followed"]);
                 } else if ($item->Owner->id==$currentUser->id){
                    $item->Owner->forcefill(['Status'=>"no"]);
                 }else{
                  $item->Owner->forcefill(['Status'=>"unfollowed"]);

                 }
             }
 if(!empty($item->comments)){
                                           $mylikesComment=Comment_like::where('User_id' , $currentUser->id)->pluck('Comment_id')->toarray();

                      $item->comments->each(function ($item,$value)use($myfollowing_id,$currentUser,$mylikesComment){
                          if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');


                }  else {
                    $item->forcefill(['ILiked' => false]);
                    $item->load('User')->load('Replaies')->load('Replaies.user');

                 }
                         $item->Replaies->each(function($item,$value)use($myfollowing_id,$currentUser){
                             
                             if(in_array($item->user->id,$myfollowing_id) ){
                         
                    $item->user->forcefill(['Status'=>"followed"]);
                 } else if ($item->user->id==$currentUser->id){
                   $item->user->forcefill(['Status'=>"no"]);
                 }else{
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                 }
                     });
                
                         });
                    
                 };;
            });
            
        
        $comments = Comment::where('Comment', 'like', '%' . $data['key'] . '%')->with('user')->with('Replaies')->with('Replaies.user')->withCount('Replaies')->get()->each(function($item,$value)use($currentUser,$mylikesComment,$myfollowing_id){
             if (in_array($item->id, $mylikesComment)) {
                    $item->forcefill(['ILiked' => true]);
                }  else {
                    $item->forcefill(['ILiked' => false]);
                 }
                if(in_array($item->user->id,$myfollowing_id) ){
                         
                    $item->user->forcefill(['Status'=>"followed"]);
                 } else if ($item->user->id==$currentUser->id){
                   $item->user->forcefill(['Status'=>"no"]);
                 }else{
                 $item->user->forcefill(['Status'=>"unfollowed"]);
                 }
        });

          $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($posts);
        $perPage = 8;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath($request->url());


        return $this->apiResponse->setSuccess("  search  success ")->setData(['Users'=>$users,
                                                                            'Posts'=>$paginatedItems,
                                                                            'Comments'=>$comments]);

    }
    public function UpdatePersonalStatus($data)
    {

        try{
            $user = GeneralHelper::getcurrentUser();
            if($user){
                $User = User::find($user->id);
                $User->personal_Status=$data['personal_Status'];
                $User->save();
                // dd($User);

            }else{
                return $this->apiResponse->setError("UnAuthorized!");
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        return $this->apiResponse->setSuccess(" successfuly")->setData($User);
    }
    public function getCommentsOfPost($data)
    {
        try{
            $user =GeneralHelper::getcurrentUser();
            $mylikesComments=Comment_like::where('User_id',$user->id)->pluck('Comment_id')->toarray();
            $mylikesReplaies=Replay_like::where('User_id',$user->id)->pluck('Replay_id')->toarray();
            $Comment = Comment::where('id',$data['Comment_id'])->with('Owner')->with(['Replaies'=> function ($query)use($mylikesComments) {
                $query->with('Owner')->get();
            }])->withcount('Replaies')->first();
                   if (in_array($Comment->id, $mylikesComments)) {
                $Comment->forcefill(['ILiked' => true]);
             } else {
                $Comment->forcefill(['ILiked' => false]);
             }
                $Comment->forcefill(['Created_at' => Carbon::parse($Comment->Created_at)->diffForHumans()]);
    
                foreach($Comment->replaies as $replay){
                      if (in_array($replay->id, $mylikesReplaies)) {
                $replay->forcefill(['ILiked' => true]);
                $replay->load('owner');
             } else {
                $replay->forcefill(['ILiked' => false]);
                $replay->load('owner');
             }
                    
                }

             
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();

        }
        
       
        return $this->apiResponse->setSuccess("Comments fetched  success ")->setData($Comment);

    }
     public function unBlockUser($data)
    {
        try{
            $user = GeneralHelper::getcurrentUser();
            $blockedUser = block::where('Blocker_id',$user->id)->where('Blocked_id',$data['Blocked_id'])->first();
            if($blockedUser){
                $blockedUser->delete();
                $follow = Follow::create(['follower' => $user->id, 'following' => $data['Blocked_id']]);
                return $this->apiResponse->setError("You UnBlocked this User successfuly")->setData();
            }else{
                
                return $this->apiResponse->setSuccess("This User has not blocked before")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }


    }
    public function WhoLikedReplay($data){
         $user = GeneralHelper::getcurrentUser();
         $replayId=$data['id'];
          $Replaylikers = Replay_like::where('Replay_id',  $replayId)->get()->pluck('User_id')->toarray();
          $users=User::wherein('id',$Replaylikers)->get();
                          return $this->apiResponse->setSuccess("People who Liked ")->setData($users);

         
    }
   public function WhoLikedQuestion($data){
         $user = GeneralHelper::getcurrentUser();
          $QuestionId=$data['id'];
          $postlikers = Post_like::where('Post_id', $QuestionId)->get()->pluck('User_id')->toarray();
          $users=User::wherein('id',$postlikers)->get();
                                    return $this->apiResponse->setSuccess("People who Liked ")->setData($users);


    }
       public function WhoLikedComment($data){
        $user = GeneralHelper::getcurrentUser();
          $myfollowing_id = Follow::where('follower', $user->id)->pluck('following')->toarray();
          $CommentId=$data['id'];
           $Commentlikers = Comment_like::where('Comment_id', $CommentId)->get()->pluck('User_id')->toarray();
          $users=User::wherein('id',$Commentlikers)->get()->each(function($item,$value)use ($myfollowing_id,$user){
                 if(in_array($item->id,$myfollowing_id) ){
                         
                    $item->forcefill(['Status'=>"followed"]);
                 } else if ($item->id==$user->id){
                   $item->forcefill(['Status'=>"no"]);
                 }else{
                 $item->forcefill(['Status'=>"unfollowed"]);
                 }
          });
                                    return $this->apiResponse->setSuccess("People who Liked ")->setData($users);

    }
     public function SharedPost($data){
        try{
            $user = GeneralHelper::getcurrentUser();
            $post = Post::where('id',$data['Post_id'])->first();
            if($post){
              $sharedPost = new Shared_Post();
              $sharedPost->User_id = $user->id;
              $sharedPost->Owner_id = $post->User_id;
              $sharedPost->Post_id = $post->id;
              $sharedPost->save();
                
                return $this->apiResponse->setSuccess("Shared Post successfuly")->setData($sharedPost);
            }else{
                
                return $this->apiResponse->setSuccess("This Post not found")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        
    }
     public function getSharedPost($data){
        try{
            $user = GeneralHelper::getcurrentUser();
             
            if($user){
               $sharedPost = Shared_Post::where('Post_id',$data['Post_id'])->with(['User','Owner','post'])->first();
                return $this->apiResponse->setError("Shared Posts Fetched successfuly")->setData($sharedPost);
            }else{
                
                return $this->apiResponse->setSuccess("This Post not found")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        
    }
    public function SharedComment($data){
        try{
            $user = GeneralHelper::getcurrentUser();
            $comment = Comment::where('id',$data['Comment_id'])->first();
            if($comment){
               $SharedComment = new Shared_Comment();
               $SharedComment->User_id = $user->id;
               $SharedComment->Owner_id = $comment->User_id;
               $SharedComment->Comment_id = $comment->id;
               $SharedComment->save();
                
                return $this->apiResponse->setSuccess("Shared Comment successfuly")->setData($SharedComment);
            }else{
                
                return $this->apiResponse->setSuccess("This Comment not found")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        
    }
    public function getSharedComment($data){
        try{
            $user = GeneralHelper::getcurrentUser();
             
            if($user){
               $sharedPost = Shared_Comment::where('Comment_id',$data['Comment_id'])->with(['User','Owner','post'])->first();
              
                
                return $this->apiResponse->setError("Shared Comments Fetched successfuly")->setData( $sharedPost);
            }else{
                
                return $this->apiResponse->setSuccess("This Comment not found")->setData();
            }
        }catch(\Exception $ex) {
            return $this->apiResponse->setError($ex->getMessage())->setData();
        }
        
    }
    // public function sharequestion($data){
    //             $user = GeneralHelper::getcurrentUser();
    //             $Post = Post::where('id','Post_id')->first();
    // }
}

