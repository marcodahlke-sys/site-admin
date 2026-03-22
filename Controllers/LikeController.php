<?php
declare(strict_types=1);

class LikeController extends App\Core\Controller
{
    public function toggle(): void
    {
        verify_csrf();

        $imageId = (int) input('image_id', 0);
        $redirectTo = (string) input('redirect_to', '/');

        $image = (new Image($this->db))->getById($imageId);

        if (!$image) {
            $this->session->flash('error', 'Bild nicht gefunden.');
            $this->redirect($redirectTo ?: '/');
        }

        $ip = client_ip();
        $likes = new Like($this->db);

        if ($likes->existsForImageAndIp($imageId, $ip)) {
            $likes->remove($imageId, $ip);
            $this->session->flash('success', 'Like entfernt.');
        } else {
            $likes->add($imageId, $ip);
            $this->session->flash('success', 'Bild geliked.');
        }

        $this->redirect($redirectTo ?: '/bild?id=' . $imageId);
    }
}