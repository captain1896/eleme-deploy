<?php
/**
 * Created by PhpStorm.
 * User: heimonsy
 * Date: 11/8/14
 * Time: 8:51 PM
 */

class TeamRepos
{
    private $keyPrefix = 'DEPLOY:S:TEAMREPOS:';
    private $teamId;
    private $repos;
    private $redis;
    private $expires;

    public function __construct($teamId)
    {
        $this->teamId = $teamId;
        $this->redis = app('redis')->connection();

        $this->expires = 432000; // 5 day

        $jstr = $this->redis->get($this->key());
        if (empty($jstr)) {
            $user = GithubLogin::getLoginUser();
            $client = new \Eleme\Github\GithubClient($user->token);
            $tempRepos = $client->request('teams/' . $teamId . '/repos');
            $this->repos = array();
            if (empty($tempRepos->message)) {
                foreach ($tempRepos as $m) {
                    if ($m->owner->login == Config::get('github.organization')) {
                        $this->repos[] = new GithubRepo(
                            $m->id,
                            $m->name,
                            $m->full_name,
                            $m->git_url
                        );
                    }
                }
                $this->save();
            } else {
                throw new Exception('teamId doesn\'t found');
            }
        } else {
            $this->repos = json_decode($jstr);
        }

    }

    public function json()
    {
        return json_encode($this->repos);
    }

    public function save()
    {
        $this->redis->set($this->key(), $this->json(), 'ex', $this->expires);
    }

    public function key()
    {
        return $this->keyPrefix . $this->teamId;
    }

    public function repos()
    {
        return $this->repos;
    }
}