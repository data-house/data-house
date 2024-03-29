<?php

namespace App\Livewire;

use App\Actions\Star\AddStar;
use App\Actions\Star\RemoveStar;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class StarButton extends Component
{

    #[Locked]
    public $model;
    

    public $showPanel = false;

    public function mount($model)
    {
        $this->model = $model;
    }

    #[Computed()]
    public function starrable()
    {
        return $this->model;
    }

    #[Computed()]
    public function userStar()
    {
        return $this->model->stars()->byUser($this->user)->first();
    }
    
    #[Computed()]
    public function starCount()
    {
        return $this->model->stars()->count();
    }
    
    #[Computed()]
    public function notes()
    {
        return $this->userStar->annotatedByAuthor()->get();
    }


    public function toggle(AddStar $add, RemoveStar $remove)
    {
        $this->resetErrorBag();

        if(is_null($this->userStar)){

            $add($this->user, $this->model);

            unset($this->userStar);
            
            unset($this->starCount);

            $this->showPanel = true;

            return;
        }

        $remove($this->user, $this->userStar);

        unset($this->userStar);

        unset($this->starCount);
    }

    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return auth()->user();
    }

    public function render()
    {
        return view('livewire.star-button');
    }
}
